/** BadliCash Checkout Widget v1.0 **/
(function(window){
  'use strict';
  var BadliCash = window.BadliCash || {};
  var ORIGIN = window.location.origin;

  BadliCash.Checkout = function(options){ this.options = options || {}; validate(this.options); };
  BadliCash.Checkout.prototype.open = function(){
    var overlay = document.createElement('div');
    overlay.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;display:flex;align-items:center;justify-content:center;';
    var modal = document.createElement('div');
    modal.style.cssText='background:#fff;border-radius:12px;width:90%;max-width:520px;overflow:hidden;';
    var iframe = document.createElement('iframe');
    iframe.style.cssText='width:100%;height:620px;border:0;';
    iframe.src = buildCheckoutUrl(this.options);
    var close = document.createElement('button');
    close.innerHTML='×'; close.style.cssText='position:absolute;top:12px;right:16px;font-size:28px;background:#fff;border:0;border-radius:50%;width:36px;height:36px;cursor:pointer;';
    close.onclick=function(){ document.body.removeChild(overlay); };
    modal.appendChild(close); modal.appendChild(iframe); overlay.appendChild(modal); document.body.appendChild(overlay);
    window.addEventListener('message', function(e){ if(e.origin!==ORIGIN) return; if(typeof options.handler==='function'){ options.handler(e.data); } });
  };

  function buildCheckoutUrl(o){
    var params = new URLSearchParams({ amount:o.amount, currency:o.currency||'INR', name:o.name||'', description:o.description||'', customer_name:o.prefill&&o.prefill.name||'', customer_email:o.prefill&&o.prefill.email||'', customer_phone:o.prefill&&o.prefill.phone||'' });
    // In a real flow you would first create a payment link via API. For MVP we open a generic checkout that reads params.
    return ORIGIN + '/pay/' + (o.link_token||'') + '?' + params.toString();
  }
  function validate(o){ if(!o) throw new Error('Options required'); if(!o.key) throw new Error('Publishable key required'); if(!o.amount||o.amount<=0) throw new Error('Amount must be > 0'); }

  window.BadliCash = BadliCash;
})(window);

 