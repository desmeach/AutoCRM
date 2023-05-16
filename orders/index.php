<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?>
    <ul class="list-inline mx-1">
        <li class="list-inline-item">
			<a class="text-center"
				<?php if ($_SERVER['REQUEST_URI'] != '/orders/' ): ?>
			   		style="text-decoration: none;"
			   	<?php endif;?>
			   href="/orders">
				Список
			</a>
		</li>
        <li class="list-inline-item"><a class="text-center" style="text-decoration: none;" href="/orders/kanban">Канбан</a></li>
    </ul>
	<?php $APPLICATION->IncludeComponent(
		"autoCRM:dataTable",
		".default",
		array(
			"COMPONENT_TEMPLATE" => ".default",
			"ENTITY" => "orders",
		),
		false
	);?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>