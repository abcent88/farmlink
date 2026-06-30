function toggleTheme(){

let current =
document.body
.getAttribute(
'data-theme'
);

let next =
current==='dark'
? 'light'
: 'dark';

document.body.setAttribute(
'data-theme',
next
);

localStorage.setItem(
'theme',
next
);

}

window.onload=()=>{

let saved=
localStorage.getItem(
'theme'
);

if(saved){

document.body
.setAttribute(
'data-theme',
saved);

}

};