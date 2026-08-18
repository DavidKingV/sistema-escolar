const sendNewPayment = async (studentId, paymentId) => {
  try {
    const response = await fetch(`${BASE_URL}/payment/sendPaymentReceipt`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        studentId,
        paymentId,
      }),
    });

    const result = await response.json();
    return result.success;
  } catch (error) {
    console.error("Error sending payment receipt:", error);
    return false;
  }
};

export { sendNewPayment };
