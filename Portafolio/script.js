// Esperar a que el DOM esté listo
document.addEventListener("DOMContentLoaded", function () {

  // Verificar que EmailJS exista
  if (typeof emailjs === "undefined") {
    console.error("❌ EmailJS no se cargó");
    return;
  }

  // Inicializar EmailJS
  emailjs.init("-lJBgn2LzsTjxEjXEY"); // 👈 PON AQUÍ TU PUBLIC KEY

  const form = document.getElementById("contactForm");
  const msg = document.getElementById("formMsg");

  if (!form) {
    console.error("❌ No se encontró el formulario");
    return;
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const nombre = document.getElementById("nombre").value.trim();
    const email = document.getElementById("email").value.trim();
    const mensaje = document.getElementById("mensaje").value.trim();

    if (!nombre || !email || !mensaje) {
      msg.style.color = "#ff6b6b";
      msg.textContent = "Completa todos los campos ❌";
      return;
    }

    emailjs.send("service_uwj6tiw", "template_v052249", {
      name: nombre,
      email: email,
      message: mensaje,
    })
    .then(function () {
      msg.style.color = "#7bff9d";
      msg.textContent = "Mensaje enviado correctamente 🚀";
      form.reset();
    })
    .catch(function (error) {
      msg.style.color = "#ff6b6b";
      msg.textContent = "Error al enviar el mensaje 😢";
      console.error("EMAILJS ERROR:", error);
    });
  });

});
