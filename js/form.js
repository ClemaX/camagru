(() => {
	"use strict";
	window.addEventListener(
		"load",
		() => {
			var forms = document.getElementsByClassName("needs-validation");
			Array.from(forms).forEach((form) => {
				form.addEventListener(
					"submit",
					(event) => {
						if (!form.checkValidity()) {
							event.preventDefault();
							event.stopPropagation();
						}

						form.classList.add("was-validated");
					},
					false
				);
			});
		},
		false
	);
})();
