<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавление элемента");
global $USER;
$user = CUser::GetByID($USER->GetID())->GetNext();
$carService = $user['UF_CARSERVICE'];
?>
<script>
	$(document).ready(function() {
		$('#form-edit').submit(function(e) {
			e.preventDefault()
			$.ajax({
				type: 'POST',
				url: '/local/php_interface/lib/Controllers/ControllerHandler.php',
				cache: false,
				data: $(this).serialize()
			}).done((response) => {
				let alert = $('.alert')
				alert.removeClass('d-none')
				if (response.error) {
					alert.removeClass('alert-success')
					alert.addClass('alert-danger')
					alert.html('Ошибка: ' + response.error)
				}
				else {
					alert.removeClass('alert-danger')
					alert.addClass('alert-success')
					alert.html('Операция прошла успешно!')
				}
			})
		})
	})
</script>
<div class="row justify-content-center">
	<h3 class="text-center">Добавление элемента</h3>
	<form class="text-center w-50" id="form-edit">
		<div class="alert alert-success d-none" role="alert">
		</div>
		<div class="my-2">
			<label for="LAST_NAME">Фамилия</label>
			<input class="form-control" value="" type="text" id="LAST_NAME" name="LAST_NAME">
		</div>
		<div class="my-2">
			<label for="NAME">Имя</label>
			<input class="form-control" value="" type="text" id="NAME" name="NAME">
		</div>
		<div class="my-2">
			<label for="SECOND_NAME">Отчество</label>
			<input class="form-control" value="" type="text" id="NAME" name="SECOND_NAME">
		</div>
		<label for="EMAIL">Email</label>
		<input class="form-control" value="" type="text" id="EMAIL" name="EMAIL">
		<label for="LOGIN">Логин</label>
		<input class="form-control" value="" type="text" id="LOGIN" name="LOGIN">
		<label for="PASSWORD">Пароль</label>
		<input class="form-control" value="" type="password" id="PASSWORD" name="PASSWORD">
		<label for="CONFIRM_PASSWORD">Подтверждение пароля</label>
		<input class="form-control" value="" type="password" id="CONFIRM_PASSWORD" name="CONFIRM_PASSWORD">
		<input type="hidden" name="GROUP_ID" value="5">
		<input type="hidden" name="UF_KEY" value="<?=getKey()?>">
		<input type="hidden" name="UF_CARSERVICE" value="<?=$carService?>">
		<input type="hidden" name="ENTITY" value="managers">
		<input type="hidden" name="ACTION" value="add">
		<button type="submit" id="submit" class="btn btn-primary mt-4">Добавить</button>
	</form>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>