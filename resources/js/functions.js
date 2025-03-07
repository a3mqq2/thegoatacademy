function getMeta(metaName) {
   const metas = document.getElementsByTagName('meta');

   for (let i = 0; i < metas.length; i++) {
       if (metas[i].getAttribute('name') === metaName) {
           return metas[i].getAttribute('content');
       }
   }

   return '';
}
function toTopPage() {
   window.scrollTo(0, 0);
}
function tolggeCollapse(id,toggle=true) {
   var myCollapse = document.getElementById(id)
   var bsCollapse = new bootstrap.Collapse(myCollapse, {
       toggle: toggle
   })
}
export { getMeta, toTopPage ,tolggeCollapse}