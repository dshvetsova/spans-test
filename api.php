<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$api = new Api();
$result = $api->getResult();
echo  json_encode($result);
exit;