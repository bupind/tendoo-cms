<?php defined('BASEPATH') or exit('No direct script access allowed');

class Gui extends CI_Model
{
    public $cols    =    array(
        1            =>    array(),
        2            =>    array(),
        3            =>    array(),
        4            =>    array(),
    );

    private $created_page    =    array();

    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Register page for dashboard
     * @params string Page Slug
     * @params Function
     * @return void
    **/
    
    public function register_page($page_slug, $function)
    {
        $this->created_page[ $page_slug ]    =    array(
            'page-slug'        =>    $page_slug,
            'function'        =>    $function
        );
    }
        
    /** 
     * Load created page
     * @params String page slug
     * @params Array params
    **/
    
    public function load_page($page_slug, $params)
    {
        // load created pages
        $this->events->do_action_ref_array('create_dashboard_pages', $params);
        // output pages

        if (riake($page_slug, $this->created_page)) {
            // loading page content
            if ($function    =    riake('function', $this->created_page[ $page_slug ])) {
                call_user_func_array($function, $params);
            } else {
                // page doesn't exists load 404 internal page error
                Html::set_title(sprintf(__('Error : Output Not Found &mdash; %s'), get('core_signature')));
                Html::set_description(__('Error page'));
                $this->load->view('dashboard/error/output-not-found');
            }
        }
        // We do convert page slug into error code key form
        elseif (in_array($page_code    =    str_replace('_', '-', $page_slug), array_keys($this->lang->language))) {
            $title            =    sprintf(__('Error : An error occured &mdash; %s'), get('core_signature'));
            $description    =    __('An Error occured');
            $msg            =    riake($page_code, $this->lang->language);
            // page doesn't exists load 404 internal page error
            Html::set_title($title);
            // Html::set_description( $description );
            $this->load->view('dashboard/error/custom', array(
                'msg'    =>    $msg
            ));
        } else {
            // page doesn't exists load 404 internal page error
            Html::set_title(sprintf(__('Error : 404 &mdash; %s'), get('core_signature')));
            Html::set_description(__('Error page'));
            $this->load->view('dashboard/error/404');
        }
    }
    
    /**
     * Page title
     * @string Page Title
    **/
    
    public function set_title($title)
    {
        Html::set_title($title);
    }
    
    /**
     * New Gui
    **/
    /**
     * Set cols width
     * 
     * col_id should be between 1 and 4. Every cols are loaded even if they width is not set
     * @access : public
     * @param : int cold id
     * @param : int width
     * @return : void
    **/
        
    public function col_width($col_id, $width)
    {
        if (in_array($col_id, array( 1, 2, 3, 4 ))) {
            $this->cols[ $col_id ][ 'width' ]    =    $width;
        }
    }
    
    /**
     * Get Col
     * 
     * @params int Col Id
     * @return bool
    **/
    
    public function get_col($col_id)
    {
        return riake($col_id, $this->cols);
    }
    
    /**
     * Add Meta to gui
     *
     * @access public
     * @param string/array namespace, config array
     * @param string meta title
     * @param string meta type
     * @param int col id
     * @return void
    **/
    
    public function add_meta($namespace, $title = 'Unamed', $type = 'box-default', $col_id = 1)
    {
        if (in_array($col_id, array( 1, 2, 3, 4 ))) {
            if (is_array($namespace)) {
                $rnamespace            =    riake('namespace', $namespace);
                $col_id                =    riake('col_id', $namespace);
                $title                =    riake('title', $namespace);
                $type                =    riake('type', $namespace);
                
                foreach ($namespace as $key => $value) {
                    $this->cols[ $col_id ][ 'metas' ][ $rnamespace ][ $key ]    =    $value;
                }
            } else {
                $this->cols[ $col_id ][ 'metas' ][ $namespace ]    =    array(
                    'namespace'        =>    $namespace,
                    'type'            =>    $type,
                    'title'            =>    $title
                );
            }
        }
    }
    
    /**
     * Add Item
     * Add item meta box
     * 
     * @params Array Config
     * @params String meta namespace
     * @params int Col id
     * @return void
    **/
    
    public function add_item($config, $metanamespace, $col_id)
    {
        if (in_array($col_id, array( 1, 2, 3, 4 )) && riake('type', $config)) {
            $this->cols[ $col_id ][ 'metas' ][ $metanamespace ][ 'items' ][]    =    $config;
        }
    }
    
    /**
     * Output
     * Output GUI content
     * @return void
    **/
    
    public function output()
    {
        $this->load->view('dashboard/header');
        $this->load->view('dashboard/horizontal-menu');
        $this->load->view('dashboard/aside');
        $this->load->view('dashboard/gui/body', array(
            'page_header'    =>    $this->load->view('dashboard/gui/page-header', array(), true),
            'cols'            =>    $this->cols
        ));
        $this->load->view('dashboard/footer');
        $this->load->view('dashboard/aside-right');
    }
    
    /**
     * 	Get GUI cols
     *	@access		:	Public
     *	@returns	:	Array
    **/
    
    public function get_cols()
    {
        return $this->cols;
    }
    
    /**
     * Allow Gui customization.
     *
     * @access public
     * @param mixed
     * @return void
    **/
    
    public function config($config)
    {
        $this->config    =    $config;
    }
}
