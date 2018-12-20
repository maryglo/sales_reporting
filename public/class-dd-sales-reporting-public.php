<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class DD_Sales_Reporting_Public {

    /**
     * @var int contactID
     */
    protected $contactID;

    /**
     * @var email
     */
    protected $email;


    public function __construct($email, $contactID) {
        $this->contactID = $contactID;
        $this->email = $email;
    }


    public function show_js(){
        add_action( 'wp_enqueue_scripts', array($this,'enqueue_scripts') );
        add_action('wp_footer', array($this,'dd_write_js'));
    }

    public function enqueue_scripts() {
        $url = $_SERVER['QUERY_STRING'];
        wp_localize_script( 'jquery' ,'dd_sales_r_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ),
                'query_string' => $url,
                'ref_url'=> $_SERVER['REDIRECT_SCRIPT_URI']
            )

        );

    }

    public function dd_write_js(){
    ?>
        <script>
            fbq('init', '413188255553523', {
                em: '<?php echo $this->email; ?>'
            });

            window.dataLayer = window.dataLayer || []
            dataLayer.push({
                <?php echo "'userID': ".$this->contactID; ?>
            });

            function getclientTracking(){
                try {
                    var tracker = ga.getAll()[0];
                    return tracker.get('clientId');
                }
                catch(e) {
                    console.log("Error fetching clientId");
                    return false;
                }
            }

            setTimeout(function(){
                var clientID = getclientTracking();
                console.log(clientID);

            }, 1000);

        </script>
    <?php
    }

    /**
     * not used for now
     */
    public function dd_sales_reporting_js(){
    ?>
        <script>


            function getclientTracking(){
                try {
                    var tracker = ga.getAll()[0];
                    return tracker.get('clientId');
                }
                catch(e) {
                    console.log("Error fetching clientId");
                    return false;
                }
            }

            jQuery(document).ready(function($){
                setTimeout(function(){
                   //var clientID = getclientTracking();
                   $.ajax({
                            url: dd_sales_r_object.ajax_url,
                            data: {action: "process_data", query_string: dd_sales_r_object.query_string, ref_url:dd_sales_r_object.ref_url },
                            method: 'post',
                            success:function(response){
                                if(response.userID){
                                    ga('set', 'userId', response.userID);
                                }

                            }
                   });

                }, 1000);

            });
        </script>
    <?php
        $output_string = ob_get_contents();
        ob_end_clean();
        echo $output_string;
    }
}

return new DD_Sales_Reporting_Public('','');