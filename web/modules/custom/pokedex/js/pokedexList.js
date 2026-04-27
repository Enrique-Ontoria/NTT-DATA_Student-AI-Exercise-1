window.addEventListener('load', function(){
    const nav = document.querySelectorAll(".button-nav");
    const pokeball = document.getElementById("pokeball");
    pokeball.style.display = "none";
    /**
     * Recorremos los 2 botones para activar el "loading" cada
     * vez que se cambia de pagina
     */
    nav.forEach(boton => {
        boton.addEventListener('click', function(e){
            e.preventDefault(); // Detenemos el pasar de pagina

            // Obtenemos la url de adonde se queria dirigir el usuario
            const destino = this.getAttribute('href');

            //Mostramos la pokeball
            if(pokeball) {
                pokeball.style.display = 'block';
            }

            // Esperamos un segundo
            setTimeout(() => {
                window.location.href = destino;
            }, 1000);
        });
    });
})