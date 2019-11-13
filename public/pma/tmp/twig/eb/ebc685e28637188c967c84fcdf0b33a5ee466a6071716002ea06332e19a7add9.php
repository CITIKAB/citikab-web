<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* privileges/add_privileges_database.twig */
class __TwigTemplate_70cb466b35621afb32185685b34f5a35f7fb45456278705fad8944f60182720e extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<label for=\"text_dbname\">";
        echo _gettext("Add privileges on the following database(s):");
        echo "</label>";
        // line 3
        if ( !twig_test_empty(($context["databases"] ?? null))) {
            // line 4
            echo "    <select name=\"pred_dbname[]\" multiple=\"multiple\">
        ";
            // line 5
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["databases"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["database"]) {
                // line 6
                echo "            <option value=\"";
                echo twig_escape_filter($this->env, PhpMyAdmin\Util::escapeMysqlWildcards($context["database"]), "html", null, true);
                echo "\">
                ";
                // line 7
                echo twig_escape_filter($this->env, $context["database"], "html", null, true);
                echo "
            </option>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['database'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 10
            echo "    </select>
";
        }
        // line 13
        echo "<input type=\"text\" id=\"text_dbname\" name=\"dbname\" />
";
        // line 14
        echo PhpMyAdmin\Util::showHint(_gettext("Wildcards % and _ should be escaped with a \\ to use them literally."));
        echo "
";
    }

    public function getTemplateName()
    {
        return "privileges/add_privileges_database.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  64 => 14,  61 => 13,  57 => 10,  48 => 7,  43 => 6,  39 => 5,  36 => 4,  34 => 3,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "privileges/add_privileges_database.twig", "/var/www/html/public/pma/templates/privileges/add_privileges_database.twig");
    }
}
