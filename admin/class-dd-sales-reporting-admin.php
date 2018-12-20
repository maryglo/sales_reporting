<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class DD_Sales_Reporting_Admin {

    /**
     * @var
     */
    protected $contactID;

    public function __construct() {
        add_action('init', array($this, 'parse_data_from_url'));

        add_action( 'admin_menu', array($this, 'add_plugin_menu'));
        register_setting( 'wp_dd_sales_reporting_settings', 'wp_dd_sales_reporting', array( $this, 'save_general_settings' ) );

        /**not used for now
        add_action( 'wp_ajax_process_data', array($this, 'dd_sales_reporting_process_data') );
        add_action( 'wp_ajax_nopriv_process_data', array($this, 'dd_sales_reporting_process_data') );
        */
    }

    public function add_plugin_menu() {
        add_options_page(
            'DD Sales Reporting',
            'DD Sales Reporting',
            'manage_options',
            'dd-sales-reporting',
            array($this, 'display_plugin_setup_page'),
            'none'
        );
    }

    public function display_plugin_setup_page(){
        $opts = dd_sales_reporting_get_options();
        $defaults = getDefaults();
        include_once( 'partials/dd-sales-reporting-display.php' );
    }

    public function save_general_settings(array $settings ){

        $current = dd_sales_reporting_get_options();

        // merge with current settings to allow passing partial arrays to this method
        $settings = array_merge( $current, $settings );

        do_action( 'wp_dd_sales_reporting_save_settings', $settings, $current );
        return $settings;

    }

    /**
     * parse data from url query string
     */
    public function parse_data_from_url(){
        $parameters = $_SERVER['QUERY_STRING'];
        $opts = dd_sales_reporting_get_options();
        $defaults = getDefaults();
        $email_vars = !empty($opts['dd_sales_reporting_email_var_names']) ? array_map('trim',explode(",", $opts['dd_sales_reporting_email_var_names'])) : array_map('trim',explode(",", $defaults['dd_sales_reporting_email_var_names']));

        parse_str( $parameters, $data );

        $data_keys = array_keys($data);
        $email_info = $this->checkIfVarNameExists($email_vars,$data_keys);

        if(!empty($data_keys)){
            if(in_array($email_info['email_key'],$data_keys) || in_array('conId', $data_keys)){

                //check if the data from query string have been stored
                $checkWith = array('email'=>$data[$email_info['email_key']]);
                $exists = $this->checkDataIfExist($checkWith);

                if($exists == false){
                   //process data
                    $contactID = $this->dd_sales_reporting_process_data($_SERVER);
                } else {
                    $existing_data = $this->getData($checkWith);
                    $contactID = in_array('conId', $data_keys) ? $data['conId']: $existing_data['contactID'];

                    //update db email with param conId
                    if(in_array('conId', $data_keys)){
                        global $wpdb;
                        $wpdb->update(
                            $wpdb->prefix . "dd_sales_data",
                            array(
                                'contactID' => (int)$data['conId'],
                            ),
                            array( 'ID' => $existing_data['ID'] ),
                            array(
                                '%d'
                            ),
                            array( '%d' )
                        );
                    }

                }

                $public = new DD_Sales_Reporting_Public($data[$email_info['email_key']],$contactID);
                $public->show_js();
            }
        }
    }

    /**
     * @param $server_data is from $_SERVER
     */
    public function dd_sales_reporting_process_data($server_data){
        global $wpdb;
        $opts = dd_sales_reporting_get_options();
        $defaults = getDefaults();
        $email_vars = !empty($opts['dd_sales_reporting_email_var_names']) ? array_map('trim',explode(",", $opts['dd_sales_reporting_email_var_names'])) : array_map('trim',explode(",", $defaults['dd_sales_reporting_email_var_names']));
        parse_str( $server_data['QUERY_STRING'], $data );

        $email_info = $this->checkIfVarNameExists($email_vars,array_keys($data));

        //post data to cURL
        $reportdata = array (
            'email' => $data[$email_info['email_key']],
            'ref_url'=>$server_data['REDIRECT_SCRIPT_URI']
        );

        if($email_info['tu_em'] !== true ){
            $reportdata['receipt'] = $data['cbreceipt'];
        }

        $contactID = $this->PostOrderData($reportdata);

        if( $contactID > 0){

            //store data to db
            unset($reportdata['receipt']);
            $reportdata['productID'] = $data['item'];
            $reportdata['contactID'] =  $contactID;
            $reportdata['date_time'] = date('Y-m-d h:i:s',$data['time']);
            $reportdata['receipt'] = $data['cbreceipt'];

            $wpdb->insert(
                $wpdb->prefix . "dd_sales_data",
                $reportdata,
                array(
                    '%s',
                    '%s',
                    '%d',
                    '%d',
                    '%s',
                    '%s'
                )
            );
        }

        return $contactID;
    }

    /**
     * @param $email_vars
     * @param $data
     * @return array
     */
    public function checkIfVarNameExists($email_vars, $data){
        $email_var_key = "";
        foreach($email_vars as $email_var){
            if(in_array($email_var, $data)){
                $email_var_key = $email_var;
                break;
            }
        }

        $tu_em = in_array('tu_em', $data);
        return array('email_key'=>$email_var_key, 'tu_em'=>$tu_em);
    }

    /**
     * @param $data as an associative array
     * @return bool
     */
    public function checkDataIfExist($data){
        global $wpdb;
        $table =  $wpdb->prefix . "dd_sales_data";
        $sql = "SELECT * FROM $table WHERE ";

        $i = 0;
        foreach($data as $key=>$value){
           $value = is_numeric($value) ? $value : "'".$value."'";
           $sql .= $i == 0 ? $key."=".$value : " AND ".$key."=".$value;
           $i++;
        }

        $result = $wpdb->get_row($sql, ARRAY_A);

        if(!empty($result)){
           return true;
        } else {
            return false;
        }
    }

    /**
     * @param $data as an associative array
     */
    public function getData($data){
        global $wpdb;
        $table =  $wpdb->prefix . "dd_sales_data";
        $sql = "SELECT * FROM $table WHERE ";

        $i = 0;
        foreach($data as $key=>$value){
            $value = is_numeric($value) ? $value : "'".$value."'";
            $sql .= $i == 0 ? $key."=".$value : " AND ".$key."=".$value;
            $i++;
        }

        $result = $wpdb->get_row($sql, ARRAY_A);
        return $result;

    }

    /**
     * @param $reportdata
     * @return int
     */
    public function PostOrderData($reportdata) {

        try {
            $reportdata['key'] = 'insert key';
            $params = http_build_query($reportdata);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,"insert url".$params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);

            $response = curl_exec($ch);
            $contactId = 0;
            if ($response !== false) {
               $data = json_decode($response);
               $contactId = isset($data->id) ? $data->id: $contactId;
            }

            // Close curl handle
            curl_close($ch);

            return $contactId;

        } catch(Exception $e) {
            trigger_error(sprintf(
                    'Curl failed with error #%d: %s',
                    $e->getCode(), $e->getMessage()),
                E_USER_ERROR);

        }

    }
}

return new DD_Sales_Reporting_Admin();