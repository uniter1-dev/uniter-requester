<?php

namespace Uniter1\UniterRequester\Application\PhpParser;

use Exception;
use PhpParser\Comment\Doc;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class RequesterParser
{
    /**
     * @throws Exception
     */
    public static function fetch(string $programText, array $newCodes, string $methodName): ?string
    {
        $tree = self::parse($programText);
        $methods = self::classMethods($tree);
        $methods = self::getNamedNodes($methods);
        $tokenPoses = self::getTokenPoses($methods);

        if (empty($tokenPoses) || empty($methods)) {
            return null;
        }

        $methodsNames = array_keys($methods);
        $suitableNames = self::suitableNames($methodsNames, $methodName);

        return self::doReplaceMethods($programText, $suitableNames, $tokenPoses, $newCodes);
    }

    /**
     * @return Stmt[]|null
     */
    public static function parse(string $programText): ?array
    {
        /** @var ParserFactory factory */
        $factory = new ParserFactory();
        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos',
            ],
        ]);
        /** @var Parser parser */
        $parser = $factory->create(ParserFactory::PREFER_PHP7, $lexer);

        return $parser->parse($programText);
    }

    /**
     * @param Node|Node[] $vertexes
     *
     * @return Node[]
     */
    public static function itemsFindInstanceOf($vertexes, string $type): array
    {
        $nodeFinder = new NodeFinder();

        return $nodeFinder->findInstanceOf($vertexes, $type);
    }

    /**
     * @return ClassMethod[]
     */
    public static function classMethods($tree): array
    {
        return self::itemsFindInstanceOf($tree, ClassMethod::class);
    }

    /**
     * @param array Node[] $nodes
     *
     * @return Node[] $nodes
     *
     * @throws Exception
     */
    public static function getNamedNodes(array $nodes): array
    {
        $res = [];
        foreach ($nodes as $node) {
            if (empty($node->name->name)) {
                throw new Exception('No name for node');
            }

            $res[$node->name->name] = $node;
        }

        return $res;
    }

    public static function doReplaceMethods(string $testText, array $methodNames, array $tokenPoses, array $newCodes): string
    {
        foreach ($methodNames as $methodName) {
            if (!array_key_exists($methodName, $newCodes)) {
                $newCodes[$methodName] = '';
            }
        }

        $replaceOffsetLength = array_map(static function ($pair) {return [$pair[0] - 1, $pair[1] - $pair[0] + 1]; }, $tokenPoses);

        return self::multiplePositionsReplace($testText, $replaceOffsetLength, $newCodes);
    }

    public static function multiplePositionsReplace(string $text, array $replaceOffsetLength, array $replacers): string
    {
        $offsetFactor = 0;
        foreach ($replacers as $replaceName => $replace) {
            if (!array_key_exists($replaceName, $replaceOffsetLength)) {
                continue;
            }

            $offset = $replaceOffsetLength[$replaceName][0];
            $length = $replaceOffsetLength[$replaceName][1];

            $newOffset = $offset + $offsetFactor;
            $additionalCharacters = self::linesLen($replace) - $length;
            $offsetFactor = $offsetFactor + $additionalCharacters;

            $text = self::linesReplace($text, $replace, $newOffset, $length);
        }

        return $text;
    }

    public static function linesLen(string $replace)
    {
        return empty($replace) ? 0 : count(explode("\n", $replace));
    }

    public static function linesReplace(string $text, string $replace, int $linesOffset, int $linesLength): string
    {
        $lines = explode("\n", $text);
        if (empty($replace)) {
            $replace = [];
        }
        array_splice($lines, $linesOffset, $linesLength, $replace);

        return implode("\n", $lines);
    }

    private static function suitableNames(array $allMethodNames, string $testMethodName): array
    {
        $suitableNames = [];
        $prefix = self::makeMethodName($testMethodName);
        foreach ($allMethodNames as $methodName) {
            if (self::isPrefix($prefix, $methodName)) {
                $suitableNames[] = $methodName;
            }
        }

        return $suitableNames;
    }

    private static function makeMethodName(string $methodName): string
    {
        return 'test'.ucfirst($methodName);
    }

    private static function isPrefix(string $prefix, string $string): bool
    {
        return 0 === strpos($string, $prefix);
    }

    public static function getTokenPoses(array $methods): array
    {
        $pairs = [];
        foreach ($methods as $name=>$method) {
            $pairs[$name] = self::getMethodOffsets($method);
        }

        return $pairs;
    }

    public static function getMethodOffsets(ClassMethod $node): ?array
    {
        $offsets = self::getNodeOffsets($node);
        $start = $offsets[0];
        $end = $offsets[1];

        $comment = self::getDocComment($node);
        if ($comment) {
            $start = $comment->getStartLine();
        }

        return [$start, $end];
    }

    public static function getNodeOffsets(Node $node): array
    {
        return [$node->getLine(), $node->getEndLine()];
    }

    public static function getDocComment(ClassMethod $node): ?Doc
    {
        return $node->getDocComment();
    }
}
