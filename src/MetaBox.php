<?php

namespace Yankov\MetaFieldsBuilder;

class MetaBox 
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;
    
    /**
     * @var Field[]
     */
    private $fields;
    
    /**
     * @var string
     */
    private $location;

    /**
     * @var int
     */
    private $page_id;

    public function __construct($id, $title, $fields, $location = 'post', $page_id = null) 
    {
        $this->id = $id;
        $this->title = $title;
        $this->fields = $fields;
        $this->location = $location;
        $this->page_id = $page_id;

        // Register the meta box.
        add_action('add_meta_boxes', [$this, 'register']);
        // Save the meta box data.
        add_action('save_post', [$this, 'save']);
    }

    /**
     * Register the meta box callback function.
     * 
     * @param string $post_type
     * @return void
     */
    public function register($post_type) : void 
    {
        $should_show = ($post_type === $this->location);

        if ($this->page_id !== null) {
            global $post;
            
            $should_show = ($should_show && $post->ID === $this->page_id);
        }

        if ($should_show) {
            add_meta_box($this->id, $this->title, [$this, 'render'], $this->location);
        }
    }

    /** 
     * Render the meta box.
     * 
     * @return void
     */
    public function render() : void 
    {
        // Add an nonce field so we can check for it later.
        wp_nonce_field('custom_meta_box', 'custom_meta_box_nonce');

        // Render the fields.
        foreach ($this->fields as $field) {
            $field->render();
        }
    }

    /**
     * Save the meta box data.
     * 
     * @param int $post_id
     * @return void
     */
    public function save($post_id) : void 
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['custom_meta_box_nonce']) || !wp_verify_nonce($_POST['custom_meta_box_nonce'], 'custom_meta_box' )) {
            return;
        }

        foreach ($this->fields as $field) {
            $field->save($post_id);
        }
    }
}