<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Console\DefinesConverter;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard as StandardPrinter;

class DefinesNodeVisitor extends NodeVisitorAbstract
{
    public function __construct()
    {
        $this->collected = [];
    }

    public function leaveNode(Node $node)
    {
        if (!$this->isDefineNode($node)) {
            return;
        }
        $args = $node->expr->args;
        $defineKey = $args[0]->value->value;
        array_shift($args);
        $prettyPrinter = new StandardPrinter;
        $defineValue = $prettyPrinter->prettyPrint($args);
        $this->collected[] = [$defineKey, $defineValue];
    }

    public function getCollected() : array
    {
        return $this->collected;
    }

    private function isDefineNode(Node $node)
    {
        if (!$node instanceof Expression) return false;
        if (!$node->expr instanceof FuncCall) return false;
        if (!$node->expr->name instanceof Name) return false;
        if (!$node->expr->name->toString() === 'define') return false;
        return true;
    }
}
