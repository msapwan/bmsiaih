$(function () {

  $('#btnSidebar').on('click', function () {
    $('#sidebar').toggleClass('show');
  });

  $(document).on('click', '.btn-hapus', function (e) {
    if (!confirm('Yakin ingin menghapus data ini? Tindakan tidak dapat dibatalkan.')) {
      e.preventDefault();
    }
  });

  $(document).on('input', '.input-uang', function () {
    var v = this.value.replace(/\D/g, '');
    this.value = v ? Number(v).toLocaleString('id-ID') : '';
  });

  $('form').on('submit', function () {
    $(this).find('.input-uang').each(function () {
      this.value = this.value.replace(/\./g, '');
    });
  });

  $('#jenis_simpanan').on('change', function () {
    var n = $(this).find(':selected').data('nominal');
    if (n) $('#jumlah').val(Number(n).toLocaleString('id-ID'));
  });

  $('#id_akad').on('change', function () {
    var tipe = $(this).find(':selected').data('tipe') || 'margin';
    $('#grupMargin').toggleClass('d-none', tipe !== 'margin');
    $('#grupNisbah').toggleClass('d-none', tipe !== 'bagihasil');
  }).trigger('change');

});