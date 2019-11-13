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

/* privileges/resource_limit_item.twig */
class __TwigTemplate_7152cd8ed75f432751c5a0ce2b31418f94e68f7d6756cb0b758e1e9ab8ff8907 extends \Twig\Template
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
        echo "<div class=\"item\">
    <label for=\"text_";
        // line 2
        echo twig_escape_filter($this->env, $this->getAttribute(($context["limit"] ?? null), "input_name", [], "array"), "html", null, true);
        echo "\">
        <code>
        <dfn title=\"";
        // line 4
        echo twig_escape_filter($this->env, $this->getAttribute(($context["limit"] ?? null), "description", [], "array"), "html", null, true);
        echo "\">
            ";
        // line 5
        echo twig_escape_filter($this->env, $this->getAttribute(($context["limit"] ?? null), "name_main", [], "array"), "html", null, true);
        echo "
        </dfn>
        </code>
    </label>
    <input type=\"number\" name=\"";
        // line 9
        echo twig_escape_filter($this->env, $this->getAttribute(($context["limit"] ?? null), "input_name", [], "array"), "html", null, true);
        echo "\" id=\"text_";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["limit"] ?? null), "input_name", [], "array"), "html", null, true);
        echo "\"
        value=\"";
        // line 10
        echo twig_escape_filter($this->env, $this->getAttribute(($context["limit"] ?? null), "value", [], "array"), "html", null, true);
        echo "\" title=\"";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["limit"] ?? null), "description", [], "array"), "html", null, true);
        echo "\" />
</div>
";
    }

    public function getTemplateName()
    {
        return "privileges/resource_limit_item.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  55 => 10,  49 => 9,  42 => 5,  38 => 4,  33 => 2,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "privileges/resource_limit_item.twig", "/var/www/html/public/pma/templates/privileges/resource_limit_item.twig");
    }
}
