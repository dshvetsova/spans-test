<?
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;

class Api
{
	private $result;
	private $cacheTime;
	private $iblockIdThemes;
	private $iblockIdWebinars;

	/**
	 * Api constructor.
	 */
	public function __construct()
	{
		$this->result = [
			'success' => false
		];
		$this->cacheTime = 3600600;
		$this->iblockIdThemes = getIblockIdByCode('type');
		$this->iblockIdWebinars = getIblockIdByCode('webinar');
	}

	public function getResult()
	{
		try {
			if (!Loader::includeModule("iblock")) {
				$text = 'При выполнении запроса произошла ошибка.';
				throw new \Exception($text);
			}

			$items = $this->prepareData();
			if (!empty($items)) {
				$this->result = [
					'success' => true,
					'items' => $items
				];
			} else {
				$text = 'Ничего не найдено.';
				throw new \Exception($text);
			}
		} catch (Exception $e) {
			$this->result['text'] = $e->getMessage();
		}
		return $this->result;
	}

	/*
	**Получает все нужные данные
	*/
	private function prepareData()
	{
		$themes = $this->prepareThemes();

		$filter = $this->makeFilter();

		$items = [];
		
		$arRes = CiblockElement::GetList(
			['SORT' => 'ASC'],
			$filter,
			false,
			false,
			['ID', 'NAME', 'PROPERTY_DATE', 'PROPERTY_TYPE']
		);
		while ($res = $arRes->GetNext()) {
			if (empty($items[$res['ID']])) {
				$items[$res['ID']] = [
					'id' => $res['ID'],
					'name' => $res['~NAME'],
					'themes' => array($themes[$res['PROPERTY_TYPE_VALUE']]),
					'date' => $res['PROPERTY_DATE_VALUE']
				];
			} else {
				$items[$res['ID']]['themes'][] = $themes[$res['PROPERTY_TYPE_VALUE']];
			}
		}
		return $items;
	}

	/*
	**Собирает фильтр, в зависимости
	**от запроса
	*/
	private function makeFilter()
	{
		$request = HttpRequest::getInput();
		$request = json_decode($request);

		$arrFilter = [
			'IBLOCK_ID' => $this->iblockIdWebinars
		];
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
			$arrFilter = array_merge($arrFilter, ['LOGIC' => 'AND'], $filterByTypePrep, $arrFilterDate);
		} elseif (empty($arrFilterDate)) {
			$arrFilter = array_merge($arrFilter, $filterByTypePrep);
		} else {
			$arrFilter = array_merge($arrFilter, $arrFilterDate);
		}
		return $arrFilter;
	}

	/*
	**Получает список тем 
	**
	*/
	private function prepareThemes()
	{
		$themes = [];
		$cache = Cache::createInstance();
		if ($cache->initCache($this->cacheTime, $this->getCacheId())) {
			$themes = $cache->GetVars();
		} elseif ($cache->startDataCache()) {
			$arRes = CiblockElement::GetList(
				['SORT' => 'ASC'],
				['IBLOCK_ID' => $this->iblockIdThemes],
				false,
				false,
				['ID', 'NAME']
			);
			while ($res = $arRes->GetNext()) {
				$themes[$res['ID']] = $res['~NAME'];
			}
			$cache->endDataCache($themes);
		}
		return $themes;
	}

	private function getCacheId()
	{
		return 'iblock_element_' . $this->iblockIdThemes;
	}

}