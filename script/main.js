function formatPrice(input) {
    // Remove non-numeric characters
    let value = input.value.replace(/[^0-9]/g, '');

    // Null handler
    if (value) {
      // Convert to float
      let floatValue = parseFloat(value);

      // Ensure the minimum value is 0
      floatValue = Math.max(0, floatValue);

      // Format number
      let formattedValue = floatValue.toLocaleString('id-ID');

      // Update input value
      input.value = formattedValue;
    } else {
      input.value = value;
    }
    
  }

function formatNoFaktur(num) {
    return num.toString().padStart(9, '0');
}