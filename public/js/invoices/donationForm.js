class DonationForm {
    denominations;
    maxDonationPrice;

    constructor(
        denominations,
        maxDonationPrice
    ) {
        this.denominations = denominations;
        this.maxDonationPrice = maxDonationPrice
    }

    submitForm() {
        var $price = $("#price").val()
        var isValid = true;
        $(".inputField").each(function () {
            const element = $(this);
            if (element.prop('required') && element.val() === "") {
                isValid = false;
            }
        });
        if (!isValid || $("#buyerRegion").val() === "-") {
            alert('Please fill in all fields')
            return
        }
        if ($price > this.maxDonationPrice) {
            alert("Your donation was larger than the allowed maximum of " + Number(this.maxDonationPrice).toFixed(2))
            return
        }
        if (isNaN($price) || $price === '') {
            alert('Please enter a donation amount')
            return
        }

        document.getElementById("donateForm").submit()
    }

    updateValue(val) {
        $(".payment").each(function () {
            $(this).removeClass("selectedPayment");
        });
        if (this.denominations.includes(val)) {
            $("#payment_" + val).addClass('selectedPayment')
        } else {
            $("#payment_other").addClass('selectedPayment')
        }
        $("#price").val(val)
    }

    updateCss() {
        $(".payment").each(function () {
            $(this).removeClass("selectedPayment");
        });
        $("#payment_other").addClass('selectedPayment')
    }
}
