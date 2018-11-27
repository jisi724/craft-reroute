<?php

namespace Craft;

class RerouteController extends BaseController
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
    	$this->requirePostRequest();
	}


	/**
	 * This action is called when a Reroute is saved
	 */
	public function actionSave()
	{
		// Is this an existing entry or a new entry?
		if ($id = craft()->request->getPost('rerouteId')) {
			$model = craft()->reroute->getById($id);
		} else {
			$model = craft()->reroute->create();
		}

		// Get the submitted data
		$data = craft()->request->getPost('reroute');
		$model->oldUrl = $data['oldUrl'];
		$model->newUrl = $data['newUrl'];
		$model->method = $data['method'];

		// Did we pass validation?
		if($model->validate()) {
			craft()->reroute->save($model);

			craft()->userSession->setNotice(Craft::t('Reroute saved.'));

			return $this->redirectToPostedUrl();
		} else {
			craft()->userSession->setError(Craft::t('There was a problem with your submission, please check the form and try again!'));
			craft()->urlManager->setRouteVariables(array('reroute' => $model));
		}
	}


	/**
	 * This action is called when a Reroute is deleted
	 * @return string json formatted
	 */
	public function actionDelete()
	{
		$this->requireAjaxRequest();

		$id = craft()->request->getRequiredPost('id');
		$result = craft()->reroute->deleteById($id);

		$this->returnJson(array('success' => $result));
  }


  	/**
	 * This action is called when a CSV been uploaded
	 */
	public function actionUpload()
	{
    $csv = array();

    // check there are no errors
    if($_FILES['csv']['error'] == 0){
      $type = $_FILES['csv']['type'];
      $tmpName = $_FILES['csv']['tmp_name'];

      // check the file is a csv
      if($type === 'text/csv'){
        if(($handle = fopen($tmpName, 'r')) !== FALSE) {
          set_time_limit(0);
          $row = 0;
          while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $col_count = count($data);
            $csv[$row]['col1'] = $data[0];
            $csv[$row]['col2'] = $data[1];
            $row++;
          }
          fclose($handle);
        }
      }
    }

    foreach ($csv as $key=>$item) {
      $model = craft()->reroute->create();

      // Get the submitted data
      $data = craft()->request->getPost('reroute');
      $model->oldUrl = $item['col1'];
      $model->newUrl = $item['col2'];
      $model->method = '301';

      // Did we pass validation?
      if($model->validate()) {
        craft()->reroute->save($model);

        craft()->userSession->setNotice(Craft::t('Reroute saved.'));

        if ($key === (sizeof($csv) - 1)) {
          return $this->redirectToPostedUrl();
        }
      } else {
        craft()->userSession->setError(Craft::t('There was a problem with your submission, please check the form and try again!'));
        craft()->urlManager->setRouteVariables(array('reroute' => $model));
      }
    }
  }
}
