<?php

namespace Morningtrain\WP\Blocks\Abstracts;


/**
 * Class Block
 *
 * @package MTTWordPressTheme\Lib\Abstracts
 *
 * @see https://www.advancedcustomfields.com/resources/blocks/
 *
 * @property string $slug           REQUIRED    The slug used for this block. Must be unique
 * @property string $title          Optional    The Block title. Defaults to ucfirst($slug)
 * @property string $description    Optional    The block description
 * @property string|Callable $template       Optional    Defaults to "{$slug}.php"
 * @property string|array $category       Optional    If array a new category will be registered. Defaults to $default_category
 * @property string|\stdClass $icon           Optional    Dashicon,svg code or object. Default is MTT logo
 * @property array|string $keywords       Optional    Keywords are useful for finding the block when searching
 * @property array $supports       Optional    The supports for the block
 * @property array $styles         Optional    Array of possible styles. Active style is available in template in $block['currentClass'] as "is-style-<STYLENAME>"
 *
 * @property bool $hasFields      Optional    Set this to false if this block does not use ACF. Usable for blocks that are static or simple doesn't require fields. Defaults to true
 *
 * @link https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
 *
 */
abstract class AbstractBlock
{

    protected static string $slug;
    protected static string $title;
    protected static string $description;

    /** @var string|Callable */
    protected static $template;

    /**
     * @var string|array
     * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#category
     */
    protected static $category;

    protected static array $default_category = [
        'slug' => 'mtt',
        'title' => 'Morning Train',
    ];

    /**
     * @var array
     * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#keywords-optional
     */
    protected static array $keywords = [
        'mtt',
        'morning train'
    ];


    /**
     * IF using dashicons exclude "dashicons-". Eg. "star-filled" for "dashicons-star-filled'"
     * @var string|\stdClass
     * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#icon-optional
     */
    protected static $icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" viewBox="0 0 89.16 100"><defs><clipPath id="clip-mtt"><rect width="89.16" height="100"/></clipPath></defs><g id="mtt" clip-path="url(#clip-mtt)"><g id="Group_9961" data-name="Group 9961" transform="translate(-303.966 -50)"><g id="Group_9955" data-name="Group 9955" transform="translate(303.966 50)"><path id="Path_6983" data-name="Path 6983" d="M28.366,23.385,42.43,44.3,58.418,23.385H86.745A51.322,51.322,0,0,0,43.766,0,52.348,52.348,0,0,0,.8,23.385Z" transform="translate(0.72)" fill="#f7941d"/><path id="Path_6984" data-name="Path 6984" d="M73.2,55.411V21L43.346,59.212v-.2L15.385,21V55.411H0a51.555,51.555,0,0,0,89.163,0Z" transform="translate(0 18.926)" fill="#f7941d"/></g></g></g></svg>';

    /**
     * @var array
     * @see https://developer.wordpress.org/block-editor/developers/block-api/block-registration/#styles-optional
     */
    protected static array $styles;

    /** @var bool */
    protected static bool $hasFields = true;


    /**
     * Register the block. This is how you initialize it
     */
    public static function register(): void
    {
        // if(function_exists( 'is_gutenberg_page' )) return; // Gutenberg is required for this
        if (!function_exists('acf_register_block_type')) {
            return;
        } // ACF Register Block Type is required for this

        static::registerActions();
        static::registerFilters();
    }

    /**
     * Registers relevant actions
     */
    protected static function registerActions(): void
    {
        \add_action('init', [static::class, 'registerBlockType']);
        // Loader::addAction('acf/init', static::class,'registerBlockType');
        if (static::hasFields()) {
            \add_action('acf/update_field_group', [static::class, 'onFieldGroupSave'], 1);
        }
    }

    /**
     * Registers relevant filters
     */
    protected static function registerFilters(): void
    {
        if (static::hasFields()) {
            \add_filter('acf/settings/load_json', [static::class, 'loadACFFolder']);
        }

        // Register category if it is an array
        $category = static::getCategory();
        if (is_array($category)) {
            \add_filter('block_categories', [static::class, 'registerBlockCategory'], 10, 2);
        }
    }

    /**
     * Get the name of this blocks dir
     *
     * @return string
     * @throws \ReflectionException
     */
    public static function getDir(): string
    {
        $reflection = new \ReflectionClass(get_called_class());
        return dirname($reflection->getFileName());
    }

    /**
     * Create directory for this blocks acf field group
     *
     * @throws \ReflectionException
     */
    public static function createAcfFieldGroupsDir(): void
    {
        $dir = static::getDir() . '/acf-field-groups';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }

    /**
     * Get the directory for this blocks acf field group
     *
     * @return string
     * @throws \ReflectionException
     */
    public static function getAcfFieldGroupsDir(): string
    {
        static::createAcfFieldGroupsDir();

        return static::getDir() . '/acf-field-groups';
    }

    /**
     * Add this blocks dir for loading field groups.
     * Used in ACF Filter
     *
     * @param $paths
     * @return mixed
     * @throws \ReflectionException
     */
    public static function loadACFFolder($paths)
    {
        $paths[] = static::getAcfFieldGroupsDir();

        return $paths;
    }

    /**
     * Sets this blocks acf field group dir as the save location IF currently saving block fields
     * Called on ACF save action
     *
     * @param $field_group
     */
    public static function onFieldGroupSave($field_group)
    {
        if (static::isBlocksfieldGroup($field_group)) {
            static::setLocalJsonLocation();
        }
    }

    /**
     * Checks if given param is a block field group
     * @param $field_group
     * @return bool
     */
    protected static function isBlocksfieldGroup($field_group): bool
    {
        if (!is_array($field_group) || empty($field_group['location'])) {
            return false;
        }
        $slug = static::getSlug();
        foreach ($field_group['location'] as $location) {
            foreach ($location as $sub_location) {
                if ($sub_location['param'] === 'block' && $sub_location['value'] === "acf/{$slug}") {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Set local JSON save location
     */
    protected static function setLocalJsonLocation()
    {
        \add_filter('acf/settings/save_json', [static::class, 'setLocalJsonSaveDir'], 99);
    }

    /**
     * Set local json save location
     * On filter callback
     *
     * @param $path
     * @return string
     * @throws \ReflectionException
     */
    public static function setLocalJsonSaveDir($path): string
    {
        return static::getAcfFieldGroupsDir();
    }


    /**
     * Returns the blocks slug.
     * Slug MUST be defined in child
     *
     * @return string
     */
    public static function getSlug(): string
    {
        return static::$slug;
    }

    /** Whether ACF field group should be loaded and handled
     * @return bool
     */
    public static function hasFields(): bool
    {
        return (bool)static::$hasFields;
    }

    /**
     * Called before getting a prop.
     *
     * @param $prop
     * @param null $default
     * @return null|mixed|void
     * @throws \ReflectionException
     */
    public static function filterProperty($prop, $default = null)
    {
        $val = static::${$prop} ?? $default;
        // TODO: Maybe reintroduce apply_filter ...
        return $val;
    }

    /**
     * Get block Title
     *
     * @return mixed|void|null
     * @throws \ReflectionException
     */
    public static function getTitle(): string
    {
        return static::filterProperty('title', ucfirst(static::getSlug()));
    }

    /**
     * Get Block description
     *
     * @return mixed|void|null
     * @throws \ReflectionException
     */
    public static function getDescription() : string
    {
        return static::filterProperty('description');
    }

    /**
     * Get template
     * Template can be a path or callable
     *
     * @return string|callable
     */
    public static function getTemplate()
    {
        return static::filterProperty('template', static::getDir() . '/' . 'template.php');
    }

    /**
     * Set block template
     *
     * @param string|callable $template
     * @return bool
     */
    public static function setTemplate($template): bool
    {
        if (!is_string($template) && !is_callable($template)) {
            return false;
        }
        static::$template = $template;

        return true;
    }

    /**
     * Returns the category
     *
     * @return string|array
     * @throws \ReflectionException
     */
    public static function getCategory()
    {
        return static::filterProperty('category', static::$default_category);
    }

    /**
     * Returns the keywords.
     * Keywords can be used when searching for this block
     *
     * @return null|array
     * @throws \ReflectionException
     */
    public static function getKeywords(): ?array
    {
        return static::filterProperty('keywords');
    }

    /**
     * Get the supports
     *
     * @return mixed|void|null
     * @throws \ReflectionException
     */
    public static function getSupports()
    {
        return static::filterProperty('supports');
    }

    /**
     * Get block icon
     *
     * @return mixed|void|null
     * @throws \ReflectionException
     */
    public static function getIcon()
    {
        return static::filterProperty('icon', 'wordpress');
    }

    /**
     * Get Styles
     * These are not CSS styles but the available styles/versions
     *
     * @return mixed|void|null
     * @throws \ReflectionException
     */
    public static function getStyles()
    {
        return static::filterProperty('styles');
    }

    /**
     * Get the block classlist
     *
     * @return string[]
     */
    public static function getClassList(): array
    {
        return [
            'mttblock',
            static::getSlug(),
        ];
        // TODO: Maybe add current blocks style class. Eg. .is-style-{style}
    }

    /**
     * Echo the block class list for use in a class=""
     * Use this in your block template!
     */
    public static function theClassList()
    {
        echo implode(' ', static::getClassList());
    }

    /**
     * Registers the Block Category
     *
     * @param $categories
     * @return array
     * @throws \ReflectionException
     *
     * @link https://developer.wordpress.org/block-editor/developers/filters/block-filters/#managing-block-categories
     */
    public static function registerBlockCategory($categories): array
    {
        return array_merge(
            $categories,
            [static::getCategory()]
        );
    }

    /**
     * Registers the block on the correct action
     * @see https://www.advancedcustomfields.com/resources/acf_register_block_type/
     *
     * @throws \Exception
     */
    public static function registerBlockType(): ?\WP_Error
    {
        if (!function_exists('acf_register_block_type')) {
            return new \WP_Error(
                'missing_acf_register_block_type',
                'ACF Function "acf_register_block_type" not found. Is ACF installed and updated?'
            );
        }

        $args = [
            'name' => static::getSlug(),
            'title' => static::getTitle(),
            'icon' => static::getIcon(),
        ];

        if (method_exists(get_called_class(), 'render')) {
            $args['render_callback'] = array(get_called_class(), 'render');
        } else {
            $args['render_template'] = static::getTemplate();
        }

        if (method_exists(get_called_class(), 'enqueueAssets')) {
            $args['enqueue_assets'] = array(get_called_class(), 'enqueueAssets');
        }

        $description = static::getDescription();
        if (!empty($description)) {
            $args['description'] = $description;
        }

        $category = static::getCategory();
        if (is_array($category)) {
            $args['category'] = $category['slug'];
        } elseif (is_string($category)) {
            $args['category'] = $category;
        }

        $keywords = static::getKeywords();
        if (!empty($keywords)) {
            $args['keywords'] = (array)$keywords;
        }

        $supports = static::getSupports();
        if (!empty($supports)) {
            $args['supports'] = (array)$supports;
        }

        $styles = static::getStyles();
        if (!empty($styles)) {
            $args['styles'] = (array)$styles;
            $args['supports']['defaultStylePicker'] = false;
        }

        \acf_register_block_type($args);
    }
}