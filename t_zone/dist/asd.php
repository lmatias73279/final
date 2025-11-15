
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<div class="form-group col-md-3">
    <label for="nombre">Nombre</label>
    <select id="nombre" name="nombre" class="form-control" value="<?= $nombre ?>">
        <option value="">Seleccione un nombre</option>
        <option value="nombre1">Nombre 1</option>
        <option value="nombre2">Nombre 2</option>
        <option value="nombre3">Nombre 3</option>
    </select>
</div>

<script>
    $(document).ready(function() {
        $('#nombre').select2();
    });
</script>
