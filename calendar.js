function openpopup(event){
    var openelement = document.querySelector('#popup');
    tip.core.DOMManipulation.addClass(openelement,'__css_popupvisible');

    let el = event.currentTarget;
    let eventId = el.dataset.eventId;

    const iframe=document.querySelector("#iframe");
    iframe.src = "http://localhost/Calendar_0601/event_detail.php?event_id="+eventId;
}
function closepopup(){
    var closeelement =document.querySelector('#popup');
    tip.core.DOMManipulation.removeClass(closeelement,'__css_popupvisible');
}
