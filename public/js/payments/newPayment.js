const sendNewPayment = async (studentId, paymentId) => {
  try {
    const response = await fetch('../../backend/payments/routes.php', { 
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'sendPaymentReceipt', 
        studentId, 
        paymentId 
      })
    });

    const result = await response.json();
    return result.success;
  } catch (error) {
    console.error('Error sending payment receipt:', error);
    return false;
  }
};

export { sendNewPayment };
