// Select tool pada tabel Items
  document.addEventListener('DOMContentLoaded', () => {
    let isMouseDown = false;
    let startX, startY;
    let dragSelect = document.createElement('div');
    dragSelect.classList.add('drag-select');
    

    // const itemsTable = document.getElementById('itemsTable');
    const itemsTable = document.getElementById('scroll-drag');
    const selectList = document.querySelector('.select-list');
    let selectedCount = 0;

    let scrollInterval = null;
    let lastMouseEvent = null;

    itemsTable.appendChild(dragSelect);

    itemsTable.addEventListener('mousedown', (e) => {
        isMouseDown = true;

        const tableRect = itemsTable.getBoundingClientRect();
        startX = e.clientX - tableRect.left + itemsTable.scrollLeft;;
        startY = e.clientY - tableRect.top + itemsTable.scrollTop;

        dragSelect.style.width = '0';
        dragSelect.style.height = '0';
        dragSelect.style.left = `${startX}px`;
        dragSelect.style.top = `${startY}px`;

        itemsTable.appendChild(dragSelect);
        
        // Save the initial mouse event
        lastMouseEvent = e;

        // Start the scroll check interval
        // scrollInterval = setInterval(checkAutoScroll, 50);
    });

    document.addEventListener('mousemove', (e) => {
        if (!isMouseDown) return;

        lastMouseEvent = e; // Update the last mouse event

        const tableRect = itemsTable.getBoundingClientRect();
        let currentX = e.clientX - tableRect.left + itemsTable.scrollLeft;
        let currentY = e.clientY - tableRect.top + itemsTable.scrollTop;

        // Constrain currentX and currentY within the table's scrollable area
        currentX = Math.max(0, Math.min(currentX, itemsTable.scrollWidth - 1));
        currentY = Math.max(0, Math.min(currentY, itemsTable.scrollHeight - 1));

        const width = Math.abs(currentX - startX);
        const height = Math.abs(currentY - startY);

        dragSelect.style.width = `${width}px`;
        dragSelect.style.height = `${height}px`;
        dragSelect.style.left = `${Math.min(currentX, startX)}px`;
        dragSelect.style.top = `${Math.min(currentY, startY)}px`;

        itemsTable.querySelectorAll('tbody tr').forEach(row => {
            const rect = row.getBoundingClientRect();
            const selectRect = dragSelect.getBoundingClientRect();

            const overlap = !(rect.right < selectRect.left ||
                              rect.left > selectRect.right ||
                              rect.bottom < selectRect.top ||
                              rect.top > selectRect.bottom);

            if (overlap) {
                row.classList.add('selected-rows');
            } else {
                row.classList.remove('selected-rows');
            }

        });

        selectedCount = itemsTable.querySelectorAll('tbody tr.selected-rows').length;
        selectList.textContent = `${selectedCount} Selected`;

        // Adjust table scroll position
        const mouseOverBottom = e.clientY > tableRect.bottom;
        const mouseOverTop = e.clientY < tableRect.top;

        if (mouseOverBottom && itemsTable.scrollTop + itemsTable.clientHeight < itemsTable.scrollHeight) {
            itemsTable.scrollTop += 10;
        } else if (mouseOverTop && itemsTable.scrollTop > 0) {
            itemsTable.scrollTop -= 10;
        }
    });

    var isDragged = false;
    document.addEventListener('mouseup', () => {
        isMouseDown = false;
        isDragged = false;
        try { itemsTable.removeChild(dragSelect); isDragged = true; } catch(err) {};
    });

      // Clear selection when clicking outside
    document.addEventListener('click', (e) => {
        if (!isDragged) {
            const isClickInsideTable = itemsTable.contains(e.target);
            if (!isClickInsideTable) {
                itemsTable.querySelectorAll('tbody tr.selected-rows').forEach(row => {
                    row.classList.remove('selected-rows');
                });
                selectedCount = 0;
                selectList.textContent = `${selectedCount} Selected`;
            }
        }

    });

    // // Function to check if the selection is at the edge and scroll
    // function checkAutoScroll() {
    //   if (!isMouseDown || !lastMouseEvent) return;

    //   const tableRect = itemsTable.getBoundingClientRect();
    //   const mouseOverBottom = lastMouseEvent.clientY > tableRect.bottom - 10;
    //   const mouseOverTop = lastMouseEvent.clientY < tableRect.top + 10;

    //   if (mouseOverBottom && itemsTable.scrollTop + itemsTable.clientHeight < itemsTable.scrollHeight) {
    //       itemsTable.scrollTop += 10;
    //   } else if (mouseOverTop && itemsTable.scrollTop > 0) {
    //       itemsTable.scrollTop -= 10;
    //   }
    // }

  });
  
  $(document).ready(function(){
    var deletedItems = [];

    // Event listener for when the modal is closed
    $('#cashierModal').on('hidden.bs.modal', function (e) {
      // Reset the deletedItems array
      deletedItems = [];
    });

    $('.cashierButton').click(function() {
      var billingId = $(this).data('billing-id');
      var tableId = $(this).data('table-id');
      var formatId = (tableId < 10) ? '0'+tableId : tableId;

      $('#saveCashierItems').data('billing-id', billingId);
      $('#cashierModalNumber').html('<div class="card-title numbered-box signature-box">' + formatId + '</div>');

      // Fetch items for the billingId
      $.ajax({
          url: '/SistemBilliard/proses/get_fnb_orders.php',
          method: 'GET',
          data: { billing_id: billingId },
          success: function(response) {
              var data = JSON.parse(response);
              var items = data.items;
              var lastId = data.lastId;

              // Update the items table
              var rows = '';
              var total = 0;
              items.forEach(function(item) {
                  rows += `<tr class="prevent-select" data-id="${item.id_order}">
                              <td>${item.nama_fnb}</td>
                              <td style="width: 20%;">${parseInt(item.harga_fnb).toLocaleString('id-ID')}</td>
                              <td style="width: 10%;">${item.jumlah_fnb}</td>
                              <td style="width: 20%;">${parseInt(item.total_fnb).toLocaleString('id-ID')}</td>
                            </tr>`;
                            var subtotal = parseFloat(item.total_fnb);
                            total += subtotal;
              });
              $('#itemsTable tbody').html(rows);
              $('#totalAmount').text(total.toLocaleString('id-ID'));

              // Store the lastId for new items
              $('#cashierModal').data('last-id', lastId);
          }
      });

    });

    $('#saveCashierItems').click(function() {
      var billingId = $(this).data('billing-id');
      var lastId = $('#cashierModal').data('last-id');
  
      var items = [];
      $('#itemsTable tbody tr').each(function() {
        var id = $(this).data('id'); // Get the id from the data-id attribute
        var item = {
            id_order: id || '', // Use id if available, otherwise set to empty string
            nama_fnb: $(this).find('td:eq(0)').text(),
            harga_fnb: $(this).find('td:eq(1)').text().replace(/\./g, ''),
            jumlah_fnb: $(this).find('td:eq(2)').text(),
            total_fnb: $(this).find('td:eq(3)').text().replace(/\./g, '')
        };
        items.push(item);
      });
  
      $.ajax({
        url: '/SistemBilliard/proses/save_fnb_orders.php',
        method: 'POST',
        data: { billing_id: billingId, items: items, deleted_items: deletedItems },
        success: function(response) {
            // Handle success response
            
            deletedItems = []; // Clear the deleted items array after saving

            //$('#saveCashierItems').data('billing-id', billingId);
            //$('#cashierModalNumber').html('<div class="card-title numbered-box signature-box">' + formatId + '</div>');

            // Fetch items for the billingId
            $.ajax({
                url: '/SistemBilliard/proses/get_fnb_orders.php',
                method: 'GET',
                data: { billing_id: billingId },
                success: function(response) {
                    var data = JSON.parse(response);
                    var items = data.items;
                    var lastId = data.lastId;
    
                    // Update the items table
                    var rows = '';
                    items.forEach(function(item) {
                        rows += `<tr class="prevent-select" data-id="${item.id_order}">
                                    <td>${item.nama_fnb}</td>
                                    <td style="width: 20%;">${parseInt(item.harga_fnb).toLocaleString('id-ID')}</td>
                                    <td style="width: 10%;">${item.jumlah_fnb}</td>
                                    <td style="width: 20%;">${parseInt(item.total_fnb).toLocaleString('id-ID')}</td>
                                  </tr>`;
                    });
                    $('#itemsTable tbody').html(rows);
    
                    // Store the lastId for new items
                    $('#cashierModal').data('last-id', lastId);
                }
            });
            updateTotal();
            alert('Item telah disimpan');
        }
      });

    });


    $('#qty').on('input', function() {
      var price = parseFloat($('#price').val().replace(/\./g, '')) || 0;
      var qty = parseFloat($('#qty').val()) || 0;
      var subtotal = price * qty;
      $('#subtotal').val(subtotal.toLocaleString('id-ID'));
    });

    $('#saveItem').click(function() {
      var productName = $('#productName').val();
      var price = parseFloat($('#price').val().replace(/\./g, '')) || 0;
      var qty = parseFloat($('#qty').val()) || 0;
      var subtotal = parseFloat($('#subtotal').val().replace(/\./g, '')) || 0;

      if(productName && price && qty && subtotal && (price > 0) && (qty > 0) && (subtotal > 0)) {
        var row = `<tr class="prevent-select">
                     <td>${productName}</td>
                     <td style="width: 20%;">${price.toLocaleString('id-ID')}</td>
                     <td style="width: 10%;">${qty}</td>
                     <td style="width: 20%;">${subtotal.toLocaleString('id-ID')}</td>
                   </tr>`;
        $('#itemsTable tbody').append(row);
        updateTotal();
      }
    });

    $('#deleteItem').click(function() {
      // $('#itemsTable tbody tr.selected-rows').remove();
      $('#itemsTable tbody tr.selected-rows').each(function() {
        var id = $(this).data('id');
        if (id) {
            deletedItems.push(id); // Track the ID of the item to be deleted
        }
        $(this).remove();
      });

      updateTotal();
      updateSelectedCount();
    });

    $('#itemsTable').on('click', 'tbody tr', function(e) {
      if (!e.shiftKey) {
        $('#itemsTable tbody tr').not($(this)).removeClass('selected-rows');
      }
      $(this).toggleClass('selected-rows');
      updateSelectedCount();
    });

    $('#productListTable').on('click', 'tbody tr', function() {
      var name = $(this).data('name');
      var price = $(this).data('price').replace(/\./g, '');
      $('#productName').val(name);
      $('#price').val(parseFloat(price).toLocaleString('id-ID'));
      $('#qty').val(1).trigger('input');
    });

    function updateSelectedCount() {
      var selectedCount = $('#itemsTable tbody tr.selected-rows').length;
      $('.select-list').text(`${selectedCount} Selected`);
    }

    $('#productListTable').on('click', 'tr', function() {
      // const cashierForm = document.getElementById("cashier-form");
      // //style
      // cashierForm.classList.add("cashier-highlight");
      // setTimeout(function() {
      //   cashierForm.classList.remove("cashier-highlight");
      // }, 500); // Durasi highlight (dalam milidetik)

      var name = $(this).data('name');
      var price = $(this).data('price').replace(/\./g, '');
      $('#productName').val(name);
      $('#price').val(parseFloat(price).toLocaleString('id-ID'));
      $('#qty').val(1).trigger('input');
    });

    $('#searchProduct').on('input', function() {
      var query = $(this).val();
      $.ajax({
        url: '/SistemBilliard/proses/cari_fnb.php',
        method: 'GET',
        data: { search: query },
        success: function(response) {
          var products = JSON.parse(response);
          var rows = '';
          products.forEach(function(product) {
            harga_menu = product.harga_menu.toLocaleString('id-ID');
            rows += `<tr data-name="${product.nama_menu}" data-price="${harga_menu}">
                      <td style="width: 70%">${product.nama_menu}</td>
                      <td style="width: 30%">${harga_menu}</td>
                    </tr>`;
          });
          $('#productListTable tbody').html(rows);
        }
      });
    });

    function updateTotal() {
      var total = 0;
      $('#itemsTable tbody tr').each(function() {
        var subtotal = parseFloat($(this).find('td:eq(3)').text().replace(/\./g, ''));
        total += subtotal;
      });
      $('#totalAmount').text(total.toLocaleString('id-ID'));
    }

    var $form = $( "#formatHarga" );
    var $input = $form.find( "input" );
    $input.on( "keyup", function( event ) {

        var price = parseFloat($('#price').val().replace(/\./g, '')) || 0;
        var qty = parseFloat($('#qty').val()) || 0;
        var subtotal = price * qty;
        $('#subtotal').val(subtotal.toLocaleString('id-ID'));
        
    } );
  });
