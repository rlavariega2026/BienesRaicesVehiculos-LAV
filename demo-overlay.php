<?php
declare(strict_types=1);

/**
 * Server-rendered DEMO identity: available immediately on published pages,
 * regardless of app.js, the browser cache, or asynchronous API requests.
 * The vector brand is traced from the exact LOGO LAV DIGITAL.png Library file.
 */
function lav_demo_markup(): string {
    return <<<'HTML'
<div id="lav-fixed-demo" aria-label="Versión de demostración de LAV Digital">
  <div class="lav-demo-brand"><img src="./assets/LOGO%20LAV%20DIGITAL.jpg" alt="LAV DIGITAL marketing y diseño digital"></div>
  <strong class="lav-demo-word">DEMO</strong>
  <div class="lav-demo-bottom">Versión de demostración · LAV Digital MKT</div>
</div>
<style id="lav-demo-server-styles">
  #lav-fixed-demo {
    position: fixed!important; inset: 0!important; z-index: 2147483600!important;
    display: flex!important; flex-direction: column!important; align-items: center!important;
    justify-content: center!important; gap: clamp(7px,2vh,22px)!important;
    pointer-events: none!important; overflow: hidden!important;
    isolation: isolate!important; opacity: 1!important; visibility: visible!important;
    font-family: Arial, Helvetica, sans-serif!important;
  }
  #lav-fixed-demo .lav-demo-brand {
    width: clamp(190px,36vw,350px)!important; max-width: min(85vw,350px)!important;
    padding: 12px 22px!important; background: rgba(255,255,255,.08)!important;
    border: 1px solid rgba(105,80,190,.12)!important; border-radius: 20px!important;
    box-shadow: none!important;
    display: flex!important; align-items: center!important; justify-content: center!important;
    backdrop-filter: blur(9px)!important;
  }
  #lav-fixed-demo .lav-demo-brand img {
    display: block!important; width: 100%!important; height: auto!important; max-height: 210px!important;
    object-fit: contain!important; opacity: .28!important; visibility: visible!important;
  }
  #lav-fixed-demo .lav-demo-word {
    display: block!important; color: rgba(111,66,215,.32)!important;
    -webkit-text-stroke: 1.5px rgba(255,255,255,.3)!important;
    text-shadow: 0 8px 26px rgba(0,0,0,.58)!important;
    font-weight: 950!important; font-size: clamp(90px,23vw,250px)!important;
    letter-spacing: -.07em!important; line-height: 1!important; transform: rotate(-14deg)!important;
    opacity: .82!important; visibility: visible!important; user-select: none!important;
  }
  #lav-fixed-demo .lav-demo-bottom {
    position: fixed!important; bottom: calc(12px + env(safe-area-inset-bottom))!important;
    left: 50%!important; transform: translateX(-50%)!important;
    padding: 10px 17px!important; max-width: calc(100vw - 30px)!important;
    border-radius: 20px!important; white-space: nowrap!important;
    background: rgba(35,19,62,.93)!important; color: #fff!important;
    font: 700 12px/1.3 Arial,Helvetica,sans-serif!important;
    box-shadow: 0 4px 12px rgba(0,0,0,.3)!important;
    text-align: center!important; opacity: 1!important;
  }
  @media(max-width:600px) {
    #lav-fixed-demo .lav-demo-brand { width: min(74vw,280px)!important; padding: 9px 14px!important; border-radius: 13px!important; }
    #lav-fixed-demo .lav-demo-word { font-size: 25vw!important; }
    #lav-fixed-demo .lav-demo-bottom { font-size: 10px!important; white-space: normal!important; }
  }
</style>
HTML;
}
