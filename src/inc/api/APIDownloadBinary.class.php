<?php

class APIDownloadBinary extends APIBasic {
  public function execute($QUERY = array()) {
    /** @var $CONFIG DataSet */
    global $FACTORIES, $CONFIG;

    if (!PQueryDownloadBinary::isValid($QUERY)) {
      $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Invalid download query!");
    }
    $this->checkToken(PActions::DOWNLOAD_BINARY, $QUERY);
    $this->updateAgent(PActions::DOWNLOAD_BINARY);

    // provide agent with requested download
    switch ($QUERY[PQueryDownloadBinary::BINARY_TYPE]) {
      case PValuesDownloadBinaryType::EXTRACTOR:
        // downloading 7zip
        $filename = "7zr" . Util::getFileExtension($this->agent->getOs());
        $path = Util::buildServerUrl() . $CONFIG->getVal(DConfig::BASE_URL) . "/static/" . $filename;
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::EXECUTABLE => $path
          )
        );
        break;
      case PValuesDownloadBinaryType::CRACKER:
        $crackerBinary = $FACTORIES::getCrackerBinaryFactory()->get($QUERY[PQueryDownloadBinary::BINARY_VERSION_ID]);
        if ($crackerBinary == null) {
          $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Invalid cracker binary type id!");
        }
        $crackerBinaryType = $FACTORIES::getCrackerBinaryTypeFactory()->get($crackerBinary->getCrackerBinaryTypeId());

        $ext = Util::getFileExtension($this->agent->getOs());
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::URL => $crackerBinary->getDownloadUrl(),
            PResponseBinaryDownload::NAME => $crackerBinaryType->getTypeName(),
            PResponseBinaryDownload::EXECUTABLE => $crackerBinary->getBinaryName() . $ext
          )
        );
        break;
      case PValuesDownloadBinaryType::PRINCE:
        $url = $CONFIG->getVal(DConfig::PRINCE_LINK);
        if(strlen($url) == 0){
          $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "No prince binary URL is configured on the server!");
        }
        $this->sendResponse(array(
            PResponseBinaryDownload::ACTION => PActions::DOWNLOAD_BINARY,
            PResponseBinaryDownload::RESPONSE => PValues::SUCCESS,
            PResponseBinaryDownload::URL => $url,
          )
        );
      default:
        $this->sendErrorResponse(PActions::DOWNLOAD_BINARY, "Unknown download type!");
    }
  }
}