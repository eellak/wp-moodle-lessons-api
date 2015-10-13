<?php
class MooCViewSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
	
    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_moocview_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
		add_filter( 'plugin_action_links_moodle-courses-view/moodle-courses-view.php' , array($this,'add_moocview_action_links'));
	}

    /**
     * Add options page
     */
    public function add_moocview_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'MooCView Settings', 
            'manage_options', 
            'moodle-courses-view', 
            array( $this, 'create_moocview_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_moocview_admin_page()
    {
        // Set class property
        $this->options['moocview_siteurl'] = get_option('moocview_siteurl');
		$this->options['moocview_sitewspath'] = get_option('moocview_sitewspath'); 
		$this->options['moocview_sitetoken'] = get_option('moocview_sitetoken');
		?>
        <div class="wrap">
            <h2>Moodle Course View settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'moocview_settings' );   
                do_settings_sections( 'moodle-courses-view' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }
	
	function add_moocview_action_links ( $links ) 
	{
		$newlinks = array(
		'<a href="' . admin_url( 'options-general.php?page=moodle-courses-view' ) . '">Settings</a>',
		);
		return array_merge( $links, $newlinks );
	}
    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'moocview_settings',
			'moocview_siteurl', // Option name
            'sanitize_text_field' // Sanitize
        );
		register_setting(
            'moocview_settings',
		    'moocview_sitewspath', // Option name
            'sanitize_text_field'  // Sanitize
        );
		register_setting(
            'moocview_settings',
		    'moocview_sitetoken', // Option name
            'sanitize_text_field' // Sanitize
        );

        add_settings_section(
            'moocview_settings_section', // ID
            'Moodle Course View Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'moodle-courses-view' // Page
        );  

        add_settings_field(
            'moocview_siteurl',
            'Moodle site', 
            array( $this, 'moocview_siteurl_callback' ), // Callback
            'moodle-courses-view', // Page
            'moocview_settings_section' // Section           
        );      

        add_settings_field(
            'moocview_sitewspath', 
            'Web service path', 
            array( $this, 'moocview_sitewspath_callback' ), 
            'moodle-courses-view', 
            'moocview_settings_section'
        );  
        
        add_settings_field(
            'moocview_sitetoken', 
            'Web service user token', 
            array( $this, 'moocview_sitetoken_callback' ), 
            'moodle-courses-view', 
            'moocview_settings_section'
        );      
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Define Moodle details:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function moocview_siteurl_callback()
    {
        printf(
            '<input type="text" id="moocview_siteurl" name="moocview_siteurl" value="%s" size="100"/>',
            isset( $this->options['moocview_siteurl'] ) ? esc_attr( $this->options['moocview_siteurl']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function moocview_sitewspath_callback()
    {
        printf(
            '<input type="text" id="moocview_sitewspath" name="moocview_sitewspath" value="%s" size="100"/>',
            isset( $this->options['moocview_sitewspath'] ) ? esc_attr( $this->options['moocview_sitewspath']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function moocview_sitetoken_callback()
    {
        printf(
            '<input type="text" id="moocview_sitetoken" name="moocview_sitetoken" value="%s" size="100"/>',
            isset( $this->options['moocview_sitetoken'] ) ? esc_attr( $this->options['moocview_sitetoken']) : ''
        );
    }
}
