<?php

/**
 * Class Preview_Backoffice_ListController
 */
class Preview_Backoffice_ListController extends Backoffice_Controller_Default
{
    public function loadAction()
    {
        $payload = [
            "title" => sprintf('%s > %s',
                __('Appearance'),
                __('Previews')),
            "icon" => "fa-desktop",
            "settings" => [
                "split_preview_actions" => filter_var(__get('split_preview_actions'), FILTER_VALIDATE_BOOLEAN)
            ],
        ];

        $this->_sendJson($payload);
    }

    public function findallAction()
    {
        $preview = new Preview_Model_Preview();
        $previews = $preview->findAll(null, ["group_by" => "aop.preview_id"]);
        $data = [];

        foreach ($previews as $preview) {
            $option = new Application_Model_Option();
            $option->find($preview->getOptionId());
            $data[] = [
                "id" => $preview->getId(),
                "title" => $preview->getTitle(),
                "feature" => $preview->getOptionId(),
                "feature_name" => $option->getName()
            ];
        }

        $this->_sendJson($data);
    }

    public function deleteAction()
    {

        try {

            if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
                $preview = new Preview_Model_Preview();
                $preview->find($data["preview_id"]);

                if ($preview->getPreviewId()) {
                    $languages = Core_Model_Language::getLanguages();
                    foreach ($languages as $language) {
                        $preview->deleteTranslation($language->getCode());
                    }
                }

                $preview->delete();

                $data = [
                    "success" => 1,
                    "message" => $this->_("Your preview has been deleted successfully.")
                ];
                $this->_sendJson($data);

            } else {
                throw new Exception($this->_("An error occurred while deleting your preview. Please try again later."));
            }
        } catch (\Exception $e) {
            $data = [
                "error" => 1,
                "message" => $e->getMessage()
            ];
            $this->_sendJson($data);
        }
    }

    public function saveSettingsAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getBodyParams();

            __set('split_preview_actions', filter_var($params['split_preview_actions'], FILTER_VALIDATE_BOOLEAN));

            $payload = [
                'success' => true,
                'message' => p__('preview', 'Settings saved!'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        $this->_sendJson($payload);
    }

}
