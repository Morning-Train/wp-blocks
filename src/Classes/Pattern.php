<?php

namespace Morningtrain\WP\Blocks\Classes;

class Pattern
{
    protected string $patternFile;
    protected string $pattern;

    protected string $title;
    protected ?string $description = null;
    protected ?int $viewportWidth = null;
    protected array $categories = [];
    protected array $blockTypes = [];
    protected array $keywords = [];

    /**
     * @param  string  $namespace
     * @param  string  $name
     *
     * @see https://developer.wordpress.org/reference/functions/register_block_pattern/
     */
    public function __construct(protected string $namespace, protected string $name)
    {
        $this->title = implode(' ', array_map('ucfirst', explode('-', $name)));
    }

    /**
     * A human-readable title for the pattern.
     * Defaults to a formatted version of $name
     *
     * @param  string  $title
     * @return $this
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Optional. Visually hidden text used to describe the pattern in the inserter.
     * A description is optional, but is strongly encouraged when the title does not fully describe what the pattern does.
     * The description will help users discover the pattern while searching.
     *
     * @param  string  $description
     * @return $this
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Optional. The intended width of the pattern to allow for a scaled preview within the pattern inserter.
     *
     * @param  int  $viewportWidth
     * @return $this
     */
    public function viewportWidth(int $viewportWidth): static
    {
        $this->viewportWidth = $viewportWidth;

        return $this;
    }

    /**
     * Optional. A list of registered pattern categories used to group block patterns. Block patterns can be shown on multiple categories.
     * A category must be registered separately in order to be used here.
     *
     * @param  array  $categories
     * @return $this
     */
    public function categories(array $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Optional. A list of block names including namespace that could use the block pattern in certain contexts (placeholder, transforms).
     * The block pattern is available in the block editor inserter regardless of this list of block names.
     * Certain blocks support further specificity besides the block name (e.g. for core/template-part you can specify areas like core/template-part/header or core/template-part/footer).
     *
     * @param  array  $blockTypes
     * @return $this
     */
    public function blockTypes(array $blockTypes): static
    {
        $this->blockTypes = $blockTypes;

        return $this;
    }

    /**
     * Optional. A list of aliases or keywords that help users discover the pattern while searching.
     *
     * @param  array  $keywords
     * @return $this
     */
    public function keywords(array $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function useTemplatePart(string $filename): static
    {
        $this->patternFile = Service::getPartsDirectory() . $filename;

        return $this;
    }

    public function usePattern(string $filename): static
    {
        $this->patternFile = Service::getPatternDirectory() . $filename;

        return $this;
    }

    public function useFile(string $file): static
    {
        $this->patternFile = $file;

        return $this;
    }

    public function useString(string $pattern): static
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function __destruct()
    {
        if (empty($this->pattern)) {
            if (empty($this->patternFile)) {
                $this->patternFile = Service::getPatternDirectory() . "/{$this->name}.html";
            }
            $this->pattern = file_get_contents($this->patternFile);
        }

        \register_block_pattern("$this->namespace/$this->name", array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->pattern,
            'viewportWidth' => $this->viewportWidth,
            'categories' => $this->categories,
            'blockTypes' => $this->blockTypes,
            'keywords' => $this->keywords,
        ]));
    }
}
