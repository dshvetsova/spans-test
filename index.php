<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");?>
<?
$request = \Bitrix\Main\HttpRequest::getInput();
$request = json_decode($request);
global $arrFilter;
if (!empty($request)) {
	if (!empty($request->data->date)) {
		foreach ($request->data->date as $monthNumber) {
			$monthNumber = (strlen($monthNumber) == 1) ? '0'.$monthNumber : $monthNumber;
			$minDate = '01.'.$monthNumber.date('.Y');
			$maxDate = date('t.', mktime(0, 0, 0, $monthNumber)).$monthNumber.date('.Y');
			$filterByDatePrep[] = [
				'LOGIC' => 'AND',
				['>=PROPERTY_DATE' => ConvertDateTime($minDate, 'YYYY-MM-DD').' 00:00:00'],
				['<=PROPERTY_DATE' => ConvertDateTime($maxDate, 'YYYY-MM-DD').' 23:59:59',]
			]; 
		}
		$arrFilterDate[] = array_merge(['LOGIC' => 'OR'], $filterByDatePrep);
	}
	if (!empty($request->data->theme)) {
		$filterByTypePrep[] = [
			'LOGIC' => 'OR',
			'PROPERTY_TYPE' => $request->data->theme
		]; 
	}
	if (!empty($arrFilterDate && !empty($filterByTypePrep))) {
		$arrFilter = array_merge(['LOGIC' => 'AND'], $filterByTypePrep, $arrFilterDate);
	} elseif (empty($arrFilterDate)) {
		$arrFilter = $filterByTypePrep;
	} else {
		$arrFilter = $arrFilterDate;
	}
}
$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "webinars",
    Array(
        "ACTIVE_DATE_FORMAT" => "d.m.Y",
        "ADD_SECTIONS_CHAIN" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "CACHE_FILTER" => "N",
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "A",
        "CHECK_DATES" => "Y",
        "FIELD_CODE" => array(),
        "FILTER_NAME" => "arrFilter",
        "IBLOCK_ID" => getIblockIdByCode('webinar'),
        "IBLOCK_TYPE" => "content",
        "NEWS_COUNT" => "10",
        "PAGER_SHOW_ALWAYS" => "Y",
        "PROPERTY_CODE" => array(
            "DATE",
            "TYPE"
        ),
        "SET_BROWSER_TITLE" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SORT_BY1" => "SORT",
        "SORT_BY2" => "ACTIVE_FROM",
        "SORT_ORDER1" => "DESC",
        "SORT_ORDER2" => "ASC",
        "AJAX" => (!empty($request)) ? 'Y' : 'N'
    )
);?>
<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>