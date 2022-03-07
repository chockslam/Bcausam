<?php

defined('ABSPATH') or die('Access denied.');

use Elementor\WPDataTables_Elementor_Widget;
use Elementor\WPDataCharts_Elementor_Widget;
use Elementor\Plugin;

class WPDataTables_Elementor_Widgets
{

    protected static $instance = null;

    public static function get_instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function init()
    {
        if (defined('ELEMENTOR_VERSION')) {
            self::get_instance();
        }
    }

    protected function __construct()
    {
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'widget_styles']);
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'widget_styles']);
        add_action('elementor/elements/categories_registered', [$this, 'register_widget_categories']);
    }

    public function includes()
    {
        require_once(WDT_ROOT_PATH . 'integrations/page_builders/elementor/widgets/class.wpDataTablesElementorWidget.php');
        require_once(WDT_ROOT_PATH . 'integrations/page_builders/elementor/widgets/class.wpDataChartsElementorWidget.php');
    }

    public function register_widgets()
    {
        $this->includes();
        if (version_compare(ELEMENTOR_VERSION, '3.5.0', '<')){
            Plugin::instance()->widgets_manager->register_widget_type(new WPDataTables_Elementor_Widget());
            Plugin::instance()->widgets_manager->register_widget_type(new WPDataCharts_Elementor_Widget());
        } else {
            Plugin::instance()->widgets_manager->register(new WPDataTables_Elementor_Widget());
            Plugin::instance()->widgets_manager->register(new WPDataCharts_Elementor_Widget());
        }
    }

    public function widget_styles()
    {
        wp_register_style('wpdt-elementor-widget-font', WDT_ROOT_URL . 'integrations/page_builders/elementor/css/style.css', array(), WDT_CURRENT_VERSION);
        wp_enqueue_style('wpdt-elementor-widget-font');
    }

    public function register_widget_categories($elements_manager)
    {
        $elements_manager->add_category(
            'wpdatatables-elementor',
            [
                'title' => 'wpDataTables',
                'icon' => 'wpdt-table-logo',
            ], 1);
    }

}





