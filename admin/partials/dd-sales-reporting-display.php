<?php
defined( 'ABSPATH' ) or exit;
?>
<div class="wrap">
    <p class="breadcrumbs">
        <span class="prefix"><?php echo __( 'You are here: ', 'ardex_widget' ); ?></span>
        <span class="current-crumb"><strong>DD Sales Reporting</strong></span>
    </p>
    <h1>
        <?php _e( 'General Settings', 'dd_sales_reporting' ); ?>
    </h1>
    <div id="poststuff">
        <div id="post-body-content">
            <form action="<?php echo admin_url( 'options.php' ); ?>" method="post" class="dd_sales_reporting_widget_settings_page">
                <?php settings_fields( 'wp_dd_sales_reporting_settings' ); ?>
                <div id="namediv" class="stuffbox">
                    <h2><label>Auth Code</label></h2>
                    <div class="inside">
                        <input placeholder="Auth Code" type="text" name="wp_dd_sales_reporting[dd_sales_reporting_auth_code]" size="30" maxlength="255" value="<?php if(!empty($opts['dd_sales_reporting_auth_code'])){ echo $opts['dd_sales_reporting_auth_code']; }else{ echo $defaults['dd_sales_reporting_auth_code']; } ?>" id="dd_sales_reporting_auth_code" />
                    </div>
                    <h2><label>Email Variable Names</label></h2>
                    <div class="inside">
                        <input placeholder="Enter comma separated variable names" type="text" name="wp_dd_sales_reporting[dd_sales_reporting_email_var_names]" size="30" maxlength="255" value="<?php if(!empty($opts['dd_sales_reporting_email_var_names'])){echo $opts['dd_sales_reporting_email_var_names']; }else { echo $defaults['dd_sales_reporting_email_var_names']; } ?>" id="dd_sales_reporting_email_var_names" />
                    </div>
                    <div class="inside dd_sales_reporting_submit_btn">
                        <?php submit_button(); ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .dd_sales_reporting_submit_btn input {
        width: 12% !important;
    }
</style>