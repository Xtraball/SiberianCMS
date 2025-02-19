<?php

class Core_Model_Statistics {

    public $logger = null;

    public function __construct() {
        $this->logger = Zend_Registry::get("logger");
    }

    public function statistics() {
        if (__get('send_statistics') !== '1') {
            $this->log('Statistics are disabled.');
        }

        if (__get('campaign_is_active') === '1') {
            try {
                $db = Zend_Db_Table::getDefaultAdapter();

                $editor_user_count = $db->fetchRow($db->select()->from("admin", array(new Zend_Db_Expr("COUNT(*) AS total"))));
                $backoffice_user_count = $db->fetchRow($db->select()->from("backoffice_user", array(new Zend_Db_Expr("COUNT(*) AS total"))));
                $apps_app_count = $db->fetchRow($db->select()->from("application", array(new Zend_Db_Expr("COUNT(*) AS total"))));
                $apps_domain_count = $db->fetchRow($db->select()->from("application", array(new Zend_Db_Expr("COUNT(*) AS total")))->where("domain IS NOT NULL"));
                if (Siberian_Version::is("PE")) {
                    $whitelabel_count = $db->fetchRow($db->select()->from("whitelabel_editor", array(new Zend_Db_Expr("COUNT(*) AS total"))));
                } else {
                    $whitelabel_count = 0;
                }
                $push_message_count = $db->fetchRow($db->select()->from("push_messages", array(new Zend_Db_Expr("COUNT(*) AS total"))));
                $android_device_count = $db->fetchRow($db->select()->from("push_gcm_devices", array(new Zend_Db_Expr("COUNT(*) AS total"))));
                $ios_device_count = $db->fetchRow($db->select()->from("push_apns_devices", array(new Zend_Db_Expr("COUNT(*) AS total"))));

                $modules_model = new Installer_Model_Installer_Module();
                $all_modules = $modules_model->findAll();
                $modules = [];
                $i = 0;
                foreach($all_modules as $module) {
                    $modules[$i++] = array(
                        "name" => $module->getData("name"),
                        "version" => $module->getVersion(),
                    );
                }

                // Features
                $application_model_option = new Application_Model_Option();
                $application_options = $application_model_option->findAll();

                $features_usage = array();
                $i = 0;
                $exists = array();
                foreach($application_options as $application_option) {
                    $count = $db->fetchRow(
                        $db->select()
                            ->from("application_option_value", array(new Zend_Db_Expr("COUNT(*) AS total")))
                            ->where("application_option_value.option_id = ?", $application_option->getId())
                            ->where("application_option_value.is_active = 1")
                    );
                    $count_active = $db->fetchRow(
                        $db->select()
                            ->from("application_option_value", array(new Zend_Db_Expr("COUNT(*) AS total")))
                            ->join("application_device", "application_device.app_id = application_option_value.app_id")
                            ->where("application_option_value.option_id = ?", $application_option->getId())
                            ->where("application_option_value.is_active = 1")
                            ->where("application_device.status_id = ?", 3)
                    );
                    $feature_name = $application_option->getData("name");

                    if(!in_array($feature_name, $exists)) {
                        $features_usage[$i++] = array(
                            "name" => $feature_name,
                            "total" => $count["total"],
                            "total_active" => $count_active["total"]
                        );
                        $exists[] = $feature_name;
                    }
                }
                unset($count, $count_active, $feature_name, $exists, $application_options);

                // Average customers per app
                $templates_usage = $db->fetchAll("
SELECT
  template_design.code as code,
  template_design.name AS name,
  COUNT(template_design.design_id) AS total
FROM application
JOIN template_design ON template_design.design_id = application.design_id
WHERE application.design_id IS NOT NULL
GROUP BY application.design_id
ORDER BY total DESC;");

                $templates = [];
                foreach ($templates_usage as $template_usage) {
                    $code = $template_usage['code'];
                    $name = $template_usage['name'];
                    $total = $template_usage['total'];

                    $templates[] = [
                        'code' => $code,
                        'name' => $name,
                        'total' => $total,
                    ];
                }

                // Average customers per app
                $average_customers = $db->fetchRow("
SELECT 
  MIN(customers) AS min,
  AVG(customers) AS avg,
  MAX(customers) AS max 
FROM (
	SELECT COUNT(*) as customers
    FROM customer
    GROUP BY app_id
) temp");

                // Average features per app
                $average_features = $db->fetchRow("
SELECT 
  MIN(features) AS min,
  AVG(features) AS avg,
  MAX(features) AS max 
FROM (
	SELECT COUNT(*) as features
    FROM application_option_value
    GROUP BY app_id
) temp");

                // Average features active per app
                $average_features_active = $db->fetchRow("
SELECT 
  MIN(features) AS min,
  AVG(features) AS avg,
  MAX(features) AS max 
FROM (
	SELECT COUNT(*) as features
    FROM application_option_value
    WHERE is_active = 1
    GROUP BY app_id
) temp");

                // Build times source
                $average_buildtime_source = $db->fetchRow("
SELECT
	MIN(build_time) as min_build_time,
    AVG(build_time) as avg_build_time,
    MAX(build_time) as max_build_time
FROM
	source_queue
WHERE status = 'success'");

                // Source Status
                $source_status = $db->fetchAll("SELECT status, COUNT(*) AS total
FROM source_queue
GROUP BY status");

                $source_statuses = array();
                foreach($source_status as $source_status) {
                    $status = $source_status["status"];
                    $total = $source_status["total"];

                    $source_statuses[$status] = $total;
                }

                // Build times apk
                $average_buildtime_apk = $db->fetchRow("
SELECT
	MIN(build_time) as min_build_time,
    AVG(build_time) as avg_build_time,
    MAX(build_time) as max_build_time
FROM
	apk_queue
WHERE status = 'success'");

                // APK Status
                $apk_status = $db->fetchAll("SELECT status, COUNT(*) AS total
FROM apk_queue
GROUP BY status");

                $apk_statuses = array();
                foreach($apk_status as $apk_status) {
                    $status = $apk_status["status"];
                    $total = $apk_status["total"];

                    $apk_statuses[$status] = $total;
                }


                // Layouts
                $application_model_layout_homepage = new Application_Model_Layout_Homepage();
                $application_layout_homepages = $application_model_layout_homepage->findAll();

                $layouts_usage = array();
                $i = 0;

                $exists = array();
                foreach($application_layout_homepages as $application_layout_homepage) {


                    $count = $db->fetchRow(
                        $db->select()
                            ->from("application", array(new Zend_Db_Expr("COUNT(*) AS total")))
                            ->where("application.layout_id = ?", $application_layout_homepage->getId())
                    );

                    $count_active = $db->fetchRow(
                        $db->select()
                            ->from("application", array(new Zend_Db_Expr("COUNT(*) AS total")))
                            ->join("application_device", "application_device.app_id = application.app_id")
                            ->where("application.layout_id = ?", $application_layout_homepage->getId())
                            ->where("application_device.status_id = ?", 3)
                    );

                    $layout_name = $application_layout_homepage->getData("name");

                    if(!in_array($layout_name, $exists)) {
                        $layouts_usage[$i++] = array(
                            "name" => $layout_name,
                            "total" => $count["total"],
                            "total_active" => $count_active["total"]
                        );

                        $exists[] = $layout_name;
                    }
                }

                $statistics = array(
                    "secret" => Core_Model_Secret::SECRET,
                    "data" => array(
                        "siberian_version"              => Siberian_Version::VERSION,
                        "siberian_installation_version" => __get("installation_version"),
                        "siberian_type"                 => Siberian_Version::TYPE,
                        "siberian_design"               => design_code(),
                        "siberian_use_https"            => true,
                        "siberian_disable_cron"         => __get("disable_cron"),
                        "siberian_environment"          => __get("environment"),
                        "siberian_update_channel"       => __get("update_channel"),
                        "siberian_cpanel_type"          => __get("cpanel_type"),
                        "siberian_analytic_app_limit"   => __get("analytic_app_limit"),
                        "siberian_signup_mode"          => __get("signup_mode"),
                        "siberian_cron_interval"        => __get("cron_interval"),
                        "siberian_node_binary_path"     => __get("node_binary_path"),
                        "siberian_installation_date"    => __get("installation_date"),
                        "siberian_licence_key"          => __get("siberiancms_key"),
                        "siberian_disk_usage"           => __get("disk_usage_cache"),
                        "siberian_letsencrypt_disabled" => __get("letsencrypt_disabled"),
                        "siberian_modules"              => $modules,
                        "siberian_features"             => $features_usage,
                        "siberian_templates"            => $templates,
                        "siberian_layouts"              => $layouts_usage,
                        "editor_user_count"             => $editor_user_count["total"],
                        "editor_whitelabel_count"       => $whitelabel_count["total"],
                        "backoffice_user_count"         => $backoffice_user_count["total"],

                        // Apps
                        "apps_app_count"                => $apps_app_count["total"],
                        "apps_with_domain"              => $apps_domain_count["total"],
                        "apps_customer_min"             => $average_customers["min"],
                        "apps_customer_max"             => $average_customers["max"],
                        "apps_customer_avg"             => $average_customers["avg"],
                        "apps_features_min"             => $average_features["min"],
                        "apps_features_max"             => $average_features["max"],
                        "apps_features_avg"             => $average_features["avg"],
                        "apps_features_active_min"      => $average_features_active["min"],
                        "apps_features_active_max"      => $average_features_active["max"],
                        "apps_features_active_avg"      => $average_features_active["avg"],

                        // Sources
                        "build_source_min"              => $average_buildtime_source["min"],
                        "build_source_max"              => $average_buildtime_source["max"],
                        "build_source_avg"              => $average_buildtime_source["avg"],
                        "build_source_status"           => $source_statuses,

                        // APK
                        "build_apk_min"                 => $average_buildtime_apk["min"],
                        "build_apk_max"                 => $average_buildtime_apk["max"],
                        "build_apk_avg"                 => $average_buildtime_apk["avg"],
                        "build_apk_status"              => $apk_statuses,

                        "push_message_count"            => $push_message_count["total"],
                        "push_android_devices"          => $android_device_count["total"],
                        "push_ios_devices"              => $ios_device_count["total"],
                    ),
                );

                $request = new Siberian_Request();
                $request->post(sprintf("https://stats.xtraball.com/campaign.php?type=%s", Siberian_Version::TYPE), $statistics);
            } catch(Exception $e){}
        } else {
            // Do nothing campaign is disabled
        }

        // Disable campaign until next.
        __set('campaign_is_active', '0');
    }

    /**
     * @param $message
     */
    public function log($message) {
        $this->logger->info(sprintf("[Core_Model_Statistics] %s", $message));
    }
}