<?php
/**
  This class needs to be instantiated as helpers, and provides all the helper 
  functionality needed by the PHP side of the API
*/
class RedEHelpers {
  private $plugin_name = 'unstoopid_include';
  private $path;
  private $css;
  private $js;
  private $templates;
  
  private $wp_template;
  private $wp_theme_root;
  
  public function __construct() {
    // README
    // I wrote this file so that I could explore
    // the WP API a bit more and get an idea
    // about where stuff is, and what functions
    // are available
    // All paths should consistently end with /
    // This is not the case with all WP functions
    // as is the case with theme_root
    
    // All we are doing here is populating some helper variables
    // for use later
    $this->path             = REDE_PLUGIN_BASE_PATH; // Why do this? Maybe a plugin wants to override this later...
    $this->css              = REDE_PLUGIN_BASE_PATH . 'templates/css/';
    $this->js               = REDE_PLUGIN_BASE_PATH . 'assets/js/'; 
    $this->templates        = REDE_PLUGIN_BASE_PATH . 'templates/';
    
    $this->wp_template      = get_template();
    // Just calling get_theme_root doesn't seem to work...
    $this->wp_theme_root    = get_theme_root( $this->wp_template );
    if ( false === strpos( $this->wp_theme_root, $this->wp_template) ) {
      $test_path = $this->wp_theme_root . '/' . $this->wp_template;
      if ( file_exists($test_path) ) {
        $this->wp_theme_root = $test_path . "/";
      }
    } else {
      $this->wp_theme_root .= '/';
    }
    
  }
  // README
  // This function finds where a template is located in the system
  // and returns an absolute path, or throws an error when it
  // is not present on the system
  public function findTemplate($template_name) {
    $test_path = $this->wp_theme_root . 'templates/' . $template_name;
    if ( file_exists( $test_path ) ) {
      return $test_path;
    } else {
      $test_path = $this->path. 'templates/' . $template_name;
      if ( file_exists($test_path) ) {
        return $test_path;
      } else {
        throw new Exception( __('Core Template was not found: ') . ' ' . $template_name );
      }
    }
  }
  /** 
    $vars_in_scope is an array like so: {'myvar' => 'some text'} which can
    be accessed in the template withe $myvar
    
    @param string template path, relative to the plugin
    @param array of key value pairs to put into scope
    @return the rendered, filtered, executed content of the php template file
  */
  public function renderTemplate($template_name, $vars_in_scope = array()) {
    global $wpdb, $user_ID, $post, $wp_query, $wp, $wp_locale;
    
    $vars_in_scope['helpers'] = $this;
    $vars_in_scope['__VIEW__'] = $template_name; //could be user-files.php or somedir/user-files.php
                                                 
    // The filter will look like: woo_commerce_json_api_vars_in_scope_for_user_files if the
    // views name was user-files.php, if it was in a subdir, like dir/user-files.php it would be dir_user_files
    $vars_in_scope = apply_filters( $this->getPluginPrefix() . '_vars_in_scope_for_' . basename( str_replace('/','_', $template_name),".php"), $vars_in_scope );
    foreach ($vars_in_scope as $name=>$value) {
      $$name = $value;
    }
    $template_path = $this->findTemplate($template_name);
    ob_start();
    try {
      include $template_path;
      $content = ob_get_contents();
      ob_end_clean();
      $content = apply_filters( $this->getPluginPrefix() . '_template_rendered_' . basename( str_replace('/','_', $template_name) ,".php") ,$content );
    } catch ( Exception $err) {
      ob_end_clean();
      throw new Exception( __('Error while rendering template ' . $template_name . ' -- ' . $err->getMessage() ) );
    }
    return $content;
  }
  /**
    Return the plugin name.
  */
  public function getPluginName() {
    return $this->plugin_name;
  }
  /*
    Get the PluginPrefix, used for meta data keys to help avoid namespace collisions
    with other plugins.
  */
  public function getPluginPrefix() {
    return str_replace('-','_',$this->plugin_name);
  }
  /*
    Does this plugin have a special text domain?
  */
  public function getPluginTextDomain() {
    return $this->getPluginName();
  }
  /***************************************************************************/
  /*                    Checkers, validators                                 */
  /***************************************************************************/
  
  /**
    We want to avoid directly accessing Array keys, because
    a) people have weird debug settings and 
    b) Some idiot thought it was a good idea to add in warnings when you access a null array key.
       Whoever that person is, they should be shot. Out of a cannon. Into the Sun.
       
    @param array to look in
    @param string key
    @param default value if not found (Default is i18n xlated to UnNamed
  */
  function orEq($array,$key,$default = null) {
    if ( $default === null ) {
      $default = __('UnNamed', $this->getPluginName() ) . ' - ' . $key;
    }
    if ( isset($array[$key]) ) {
      return $array[$key];
    }
    return $default;
  }
  /**
    PHP's array_search is clumsy and not helpful with simple searching where all we want
    is a true or false value. It's just easier to do it our own way.
  */
  public function inArray($needle, $haystack) {
    foreach ($haystack as $value) {
      if ($needle === $value) {
        return true;
      }
    }
    return false;
  }
  /***************************************************************************/
  /*                         HTML API Helpers                                */
  /***************************************************************************/
  public function labelTag($args) {
    $name = $this->orEq($args,'name');
    $content = $this->orEq($args,'label');
    $classes = $this->orEq($args,'classes','');
    return "<label for='" . esc_attr( $name ) . "' for='" . esc_attr( $classes ) . "'>" . esc_html( $content ) . "</label>";
  }
  public function inputTag($args) {
    $name = $this->orEq($args,'name');
    $value = $this->orEq($args,'value','');
    $id = $this->orEq($args,'id','');
    return "<input type='text' id='" . esc_attr($id) . "' name='" . esc_attr( $name ) . "' value='" . esc_html( $value ) . "' />";
  }
  public function textAreaTag($args) {
    $name = $this->orEq($args,'name');
    $value = $this->orEq($args,'value','');
    $id = $this->orEq($args,'id','');
    $rows = $this->orEq($args,'rows',3);
    return "<textarea id='" . esc_attr($id) . "' name='" . esc_attr( $name ) . "' rows='" . esc_attr( $rows ) . "'>" . esc_html( $value ) . "</textarea>";
  }
  
  /***************************************************************************/
  /*                       WordPress API Helpers                             */
  /***************************************************************************/
  
  /**
    Convert a title into a slug
  */
  public function createSlug($text) {
    $text = sanitize_title($text);
    return $text;
  }
  /**
     We want to ease the creation of pages
     
     @param $title - The title you want to use, will be converted to the slug
     @param $content - the contents of the page
     @param $publish - boolean
     @return Array of populated values to send to insert_post
  */
  public function newPage($title,$content,$publish = true) {
    $page = array(
			'post_status' 		=> $publish === true ? 'publish' : 'pending',
			'post_type' 		=> 'page',
			'post_author' 		=> 1,
			'post_name' 		=> $this->createSlug($title),
			'post_title' 		=> $title,
			'post_content' 		=> $content,
			'post_parent' 		=> 0,
			'comment_status' 	=> 'closed'
		);
    return $page;
  }
  
}
?>
