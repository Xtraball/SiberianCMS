# # App

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [optional] [readonly]
**name** | **string** | The name of your app, as displayed on your apps list on the dashboard.  This can be renamed. | [optional]
**players** | **int** |  | [optional] [readonly]
**messageable_players** | **int** |  | [optional] [readonly]
**updated_at** | **\DateTime** |  | [optional] [readonly]
**created_at** | **\DateTime** |  | [optional] [readonly]
**android_gcm_sender_id** | **string** | Android: Your Google Project number.  Also known as Sender ID. | [optional]
**gcm_key** | **string** | Android: Your Google Push Messaging Auth Key | [optional]
**chrome_web_origin** | **string** | Chrome (All Browsers except Safari) (Recommended): The URL to your website.  This field is required if you wish to enable web push and specify other web push parameters. | [optional]
**chrome_key** | **string** | Not for web push.  Your Google Push Messaging Auth Key if you use Chrome Apps / Extensions. | [optional]
**chrome_web_default_notification_icon** | **string** | Chrome (All Browsers except Safari): Your default notification icon. Should be 256x256 pixels, min 80x80. | [optional]
**chrome_web_sub_domain** | **string** | Chrome (All Browsers except Safari): A subdomain of your choice in order to support Web Push on non-HTTPS websites. This field must be set in order for the chrome_web_gcm_sender_id property to be processed. | [optional]
**apns_env** | **string** | iOS: Either sandbox or production | [optional]
**apns_p12** | **string** | iOS: Your apple push notification p12 certificate file, converted to a string and Base64 encoded. | [optional]
**apns_p12_password** | **string** | iOS: Required if using p12 certificate.  Password for the apns_p12 file. | [optional]
**apns_certificates** | **string** |  | [optional] [readonly]
**safari_apns_certificates** | **string** |  | [optional] [readonly]
**safari_apns_p12** | **string** | Safari: Your apple push notification p12 certificate file for Safari Push Notifications, converted to a string and Base64 encoded. | [optional]
**safari_apns_p12_password** | **string** | Safari: Password for safari_apns_p12 file | [optional]
**safari_site_origin** | **string** | Safari (Recommended): The hostname to your website including http(s):// | [optional]
**safari_push_id** | **string** |  | [optional] [readonly]
**safari_icon_16_16** | **string** |  | [optional] [readonly]
**safari_icon_32_32** | **string** |  | [optional] [readonly]
**safari_icon_64_64** | **string** |  | [optional] [readonly]
**safari_icon_128_128** | **string** |  | [optional] [readonly]
**safari_icon_256_256** | **string** | Safari: A url for a 256x256 png notification icon. This is the only Safari icon URL you need to provide. | [optional]
**site_name** | **string** | All Browsers (Recommended): The Site Name. Requires both chrome_web_origin and safari_site_origin to be set to add or update it. | [optional]
**basic_auth_key** | **string** |  | [optional] [readonly]
**organization_id** | **string** | The Id of the Organization you would like to add this app to. | [optional]
**additional_data_is_root_payload** | **bool** | iOS: Notification data (additional data) values will be added to the root of the apns payload when sent to the device.  Ignore if you&#39;re not using any other plugins, or not using OneSignal SDK methods to read the payload. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
