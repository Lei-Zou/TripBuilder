
window.onload = function(){
    let item = document.getElementById('rd_wayType2');
    if (item.checked) {
        document.getElementById('div_returnDate').style.display = "block";
        document.getElementById('div_sortTime1').style.display = "none";
        document.getElementById('div_sortTime2').style.display = "none";

    } else {
        document.getElementById('div_returnDate').style.display = "none";
        document.getElementById('div_sortTime1').style.display = "block";
        document.getElementById('div_sortTime2').style.display = "block";
    }
}

document.getElementById('rd_wayType1').onchange = function () {
    let item = document.getElementById('rd_wayType1');
    if (item.checked) {
        document.getElementById('div_returnDate').style.display = "none";
        document.getElementById('div_sortTime1').style.display = "block";
        document.getElementById('div_sortTime2').style.display = "block";
    } else {
        document.getElementById('div_returnDate').style.display = "block";
        document.getElementById('div_sortTime1').style.display = "none";
        document.getElementById('div_sortTime2').style.display = "none";
    }
}
document.getElementById('rd_wayType2').onchange = function () {
    var item = document.getElementById('rd_wayType2');
    if (item.checked) {
        document.getElementById('div_returnDate').style.display = "block";
        document.getElementById('div_sortTime1').style.display = "none";
        document.getElementById('div_sortTime2').style.display = "none";
    } else {
        document.getElementById('div_returnDate').style.display = "none";
        document.getElementById('div_sortTime1').style.display = "block";
        document.getElementById('div_sortTime2').style.display = "block";
    }
}