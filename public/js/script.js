const passwordInput = document.querySelector('#registration_form_plainPassword_first')
const changePasswordInput = document.querySelector('#change_password_form_plainPassword_first')

// const regex = /^(?=.*[!@#$%^&*()\[\]{};:<>|.\/?])(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{12,}$/
const caractere = document.querySelector('.caractere')
const majuscule = document.querySelector('.majuscule')
const minuscule = document.querySelector('.minuscule')
const chiffre = document.querySelector('.chiffre')
const longueur = document.querySelector('.longueur')
const regexCaractere = /(?=.*[!@#$%^&*()\[\]{};:<>|.\/?])/
const regexMajuscule = /(?=.*[A-Z])/
const regexMinuscule = /(?=.*[a-z])/
const regexChiffre = /(?=.*[0-9])/
const regexLongeur = /.{12,}/


function updatePasswordValidation(input, caractereElem, majusculeElem, minusculeElem, chiffreElem, longueurElem, regexCaractere, regexMajuscule, regexMinuscule, regexChiffre, regexLongeur) {
    input.addEventListener('input', function(){
        if(regexCaractere.test(this.value)){
            caractereElem.classList.add('success')
        }else{
            caractereElem.classList.remove('success')
        }

        if(regexMajuscule.test(this.value)){
            majusculeElem.classList.add('success')
        }else{
            majusculeElem.classList.remove('success')
        }

        if(regexMinuscule.test(this.value)){
            minusculeElem.classList.add('success')
        }else{
            minusculeElem.classList.remove('success')
        }

        if(regexChiffre.test(this.value)){
            chiffreElem.classList.add('success')
        }else{
            chiffreElem.classList.remove('success')
        }

        if(regexLongeur.test(this.value)){
            longueurElem.classList.add('success')
        }else{
            longueurElem.classList.remove('success')
        }
    });
}

// Appliquer la fonction à passwordInput
if (passwordInput !== null) {
updatePasswordValidation(
    passwordInput,
    caractere,
    majuscule,
    minuscule,
    chiffre,
    longueur,
    regexCaractere,
    regexMajuscule,
    regexMinuscule,
    regexChiffre,
    regexLongeur
);
}

// Appliquer la fonction à changePasswordInput
updatePasswordValidation(
    changePasswordInput,
    caractere,
    majuscule,
    minuscule,
    chiffre,
    longueur,
    regexCaractere,
    regexMajuscule,
    regexMinuscule,
    regexChiffre,
    regexLongeur
);







