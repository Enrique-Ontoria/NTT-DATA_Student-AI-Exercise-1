window.addEventListener('load', function(){
    const btn_evo = document.querySelector(".btn-evo");
    const box_evo = document.querySelector(".evolutions");
    box_evo.style.display = 'none';

    btn_evo.addEventListener('click', function(){
        if (box_evo.style.display !== 'none') {
            box_evo.style.display = 'none';
        }
        else {
            box_evo.style.display = 'flex';
        }
    })
})