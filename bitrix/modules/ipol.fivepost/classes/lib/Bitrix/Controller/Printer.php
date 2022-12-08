<?
namespace Ipol\Fivepost\Bitrix\Controller;

use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\Fivepost\FivepostApplication;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

/**
 * Class Printer
 * @package namespace Ipol\Fivepost\Bitrix\Controller
 */
class Printer extends AbstractController
{
    public function __construct()
    {
        parent::__construct(IPOL_FIVEPOST, IPOL_FIVEPOST_LBL);
    }

    /**
     * Get sticker files
     * @param string[] $guids Fivepost UUIDs array
     * @return \Bitrix\Main\Result
     */
    public function getStickers($guids)
    {
        $result = new Result();

        $this->application->setTimeout(30);
        $this->application->setCache(null);
        $answer = $this->application->getOrderLabels($guids, FivepostApplication::ORDER_ID_TYPE_5P);
        if ($answer->isSuccess()) {
            $stickerData = [];
            $resultsCollection = $answer->getResponse()->getOrderLabelsResults();
            $resultsCollection->reset();
            while ($orderLabel = $resultsCollection->getNext()) {
                $orderId = $orderLabel->getOrderId();
                if ($orderLabel->isSuccess()) {
                    $filename = 'sticker_'.$orderLabel->getFileName();
                    if ($file = $this->saveToFile($orderLabel->getFileContent(), $filename)) {
                        $stickerData['FILES'][] = $file;
                    } else {
                        $stickerData['ERRORS'][] = Tools::getMessage('ERR_CAN_NOT_SAVE_STICKER_FILE').$filename;
                    }
                } else {
                    $stickerData['ERRORS'][] = str_replace("#ERROR#", $orderLabel->getReason(), Tools::getMessage('ERR_STICKER_FILE_NOT_GENERATED')).$orderId;
                }
            }

            if (!empty($stickerData['FILES'])) {
                // At least one sticker generated successfully
                $result->setData($stickerData);
            } else {
                foreach ($stickerData['ERRORS'] as $error) {
                    $result->addError(new Error($error));
                }
            }
        } else {
            if ($this->application->getErrorCollection()) {
                $this->application->getErrorCollection()->reset();
                while ($error = $this->application->getErrorCollection()->getNext()) {
                    $result->addError(new Error($error->getMessage()));
                }
            } else
                $result->addError(new Error('Error while getting stickers from API, but no error messages get from application.'));
        }

        return $result;
    }

    /**
     * Make and return files upload directory
     * @param bool $noDocumentRoot
     * @return string
     */
    public static function getFilePath($noDocumentRoot = false)
    {
        $uploadPath = '/upload/ipol.fivepost/';

        if (!file_exists($_SERVER['DOCUMENT_ROOT'].$uploadPath))
            mkdir($_SERVER['DOCUMENT_ROOT'].$uploadPath);

        return (($noDocumentRoot) ? '' : $_SERVER['DOCUMENT_ROOT']).$uploadPath;
    }

    /**
     * Save data to file
     * @param mixed $data
     * @param string $filename
     * @return string|false
     */
    protected function saveToFile($data, $filename)
    {
        return (file_put_contents(self::getFilePath().$filename, $data) ? self::getFilePath(true).$filename : false);
    }

    /**
     * Make hash from given ids
     * @param string|string[] $ids
     * @return string
     */
    protected function makeHash($ids)
    {
        if (!is_array($ids))
            $ids = array($ids);

        return md5(implode('|', $ids));
    }
}