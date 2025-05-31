if (typeof jQuery != "undefined") {
  $(document).ready(function () {
    const indonesianLanguage = {
      emptyTable: "Tidak ada data yang tersedia pada tabel ini",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
      infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
      infoFiltered: "(disaring dari _MAX_ total data)",
      lengthMenu: "Tampilkan _MENU_ data",
      loadingRecords: "Memuat...",
      processing: "Memproses...",
      search: "Cari:",
      zeroRecords: "Tidak ditemukan data yang sesuai",
      paginate: {
        first: "Pertama",
        last: "Terakhir",
        next: "Selanjutnya",
        previous: "Sebelumnya",
      },
    };

    $(".datatable").DataTable({
      language: indonesianLanguage,
      responsive: true,
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "Semua"],
      ],
    });

    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get("page");

    if (currentPage === "receivables") {
      $("#dataTable").DataTable({
        language: indonesianLanguage,
        responsive: true,
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, "Semua"],
        ],
        dom: "lrtip",
      });
    } else {
      $("#dataTable").DataTable({
        language: indonesianLanguage,
        responsive: true,
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, "Semua"],
        ],
      });
    }

    $(".currency").on("input", function () {
      const value = this.value.replace(/[^\d]/g, "");
      this.value = formatRupiah(value);
    });

    $("form").on("submit", function (e) {
      let isValid = true;

      $(this)
        .find("[required]")
        .each(function () {
          if ($(this).val() === "") {
            isValid = false;
            $(this).addClass("is-invalid");
          } else {
            $(this).removeClass("is-invalid");
          }
        });

      if (!isValid) {
        e.preventDefault();
        alert("Mohon lengkapi semua field yang wajib diisi!");
      }
    });

    setTimeout(() => {
      $(".alert").fadeOut();
    }, 5000);
  });
}

function formatRupiah(angka) {
  const number_string = angka.replace(/[^,\d]/g, "").toString();
  const split = number_string.split(",");
  const sisa = split[0].length % 3;
  let rupiah = split[0].substr(0, sisa);
  const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

  if (ribuan) {
    const separator = sisa ? "." : "";
    rupiah += separator + ribuan.join(".");
  }

  rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
  return rupiah;
}

function confirmDelete(
  message = "Apakah Anda yakin ingin menghapus data ini?"
) {
  return confirm(message);
}

function printDiv(divId) {
  const printContents = document.getElementById(divId).innerHTML;
  const originalContents = document.body.innerHTML;

  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;

  setTimeout(() => {
    location.reload();
  }, 1000);
}

function exportToExcel(tableId, filename = "export") {
  alert("Fitur export akan segera tersedia!");
}

function validateStock(productId, requestedQty) {
  return true;
}

$(document).on("change", ".product-select", function () {
  const selectedOption = $(this).find("option:selected");
  const price = selectedOption.data("price");
  const stock = selectedOption.data("stock");
  const row = $(this).closest(".row, tr");

  row.find(".price-input").val(price);

  row.find(".quantity-input").attr("max", stock);

  calculateSubtotal(row);
});

$(document).on("input", ".quantity-input", function () {
  const row = $(this).closest(".row, tr");
  calculateSubtotal(row);
});

$(document).on("input", ".price-input", function () {
  const row = $(this).closest(".row, tr");
  calculateSubtotal(row);
});

function calculateSubtotal(row) {
  const price = Number.parseFloat(row.find(".price-input").val()) || 0;
  const quantity = Number.parseFloat(row.find(".quantity-input").val()) || 0;
  const subtotal = price * quantity;

  row.find(".subtotal").val(formatRupiah(subtotal.toString()));

  calculateTotal();
}

function calculateTotal() {
  let total = 0;

  $(".subtotal").each(function () {
    const subtotalValue = $(this).val().replace(/[^\d]/g, "");
    total += Number.parseFloat(subtotalValue) || 0;
  });

  $("#total_amount").val(formatRupiah(total.toString()));
}

function addNewItem(containerId, templateClass) {
  const template = $("." + templateClass + ":first").clone();
  template.find("input, select").val("");
  template.find(".subtotal").val("0");

  if (!template.find(".remove-item").length) {
    template.append(
      '<div class="col-auto"><button type="button" class="btn btn-sm btn-danger remove-item mt-4"><i class="fas fa-trash"></i></button></div>'
    );
  }

  $("#" + containerId).append(template);
}

$(document).on("click", ".remove-item", function () {
  $(this).closest(".row, tr").remove();
  calculateTotal();
});

function resetForm(formId) {
  $("#" + formId)[0].reset();
  $("#" + formId)
    .find(".is-invalid")
    .removeClass("is-invalid");
}

function showLoading(element) {
  $(element)
    .prop("disabled", true)
    .html('<i class="fas fa-spinner fa-spin"></i> Loading...');
}

function hideLoading(element, originalText) {
  $(element).prop("disabled", false).html(originalText);
}

function printReceivablesTable() {
  setTimeout(function () {
    window.print();
  }, 100);
}
