<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arParams['AJAX'] == 'Y') {
	$APPLICATION->RestartBuffer();
}
?>
<div class="container bg-white">
	<div class="row row-cols-3 p-3 mt-2">
		<?if (empty($arResult['ITEMS'])) : ?>
			<div class="col">К сожалению, ничего не найдено.</div>
		<?else : ?>
			<?foreach ($arResult['ITEMS'] as $item) : ?>
				<div class="col">
					<div class="p-3 rounded bg-light mx-2 py-4 mt-2">
						<h3><?=$item['~NAME'];?></h3>
						<?foreach ($item['DISPLAY_PROPERTIES']['TYPE']['LINK_ELEMENT_VALUE'] as $linkedValue) : ?>
							<a><?=$linkedValue['~NAME']?></a>
						<?endforeach;?>
						<p><?=$item['DISPLAY_PROPERTIES']['DATE']['~VALUE']?></p>
					</div>
				</div>
			<?endforeach;?>
		<?endif;?>
	</div>
</div>
<?if ($arParams['AJAX'] == 'Y') {
	die();
}
?>