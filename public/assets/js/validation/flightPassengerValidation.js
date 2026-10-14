
function isValidFlightPassengerPhoneNumber(phoneNumber) {
    const phoneNumberPattern = /^(06|07)\d{8}$/;
    return phoneNumberPattern.test(phoneNumber);
}
