<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавить");
?>

<form method="post">
    <div class="container col justify-content-center w-75 text-center">
        <h3>Добавление услуги</h3>
        <label for="name">Название</label>
        <input  class="form-control" id="name" name="name" type="text">
        <label for="branch">Автосервис</label>
        <select class="form-select" name="branch" id="branch">
            <option value="1">Автосервис А</option>
        </select>
        <label for="branch">Цена</label>
        <input class="form-control" type="text" placeholder="Цена" name="price" id="price">
        <label for="branch">Нормо-час</label>
        <input class="form-control" type="text" placeholder="Нормо-час" name="working_hour" id="working_hour">
        <input class="btn btn-primary mt-3" type="submit" value="Добавить" name="add_elem">
    </div>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>