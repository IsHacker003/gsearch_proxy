// Intercept and block tracking urls by hooking into XHR and fetch methods

// XHR
// Taken from https://stackoverflow.com/questions/74939082/how-to-block-loading-of-a-specific-js-file
(function() {
    let _open = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
       _open.call(this, method, url, async=async, user=user, password=password)
    }
    let _send = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.send = function(data) {
        let _onload  = this.onprogress;
        this.onload = function() {
	console.log("Allowed: "+this.responseURL);
            if (_onload != null) {
                _onload.call(this)
            }
        }
	if (data != null && data.startsWith("[")) {
	       console.log("Blocked tracking request!");
	}
        else {
	   _send.call(this, data);
	}
    }
})();

// fetch
const { fetch: originalFetch } = window;
window.fetch = async (...args) => {
    let [resource, config ] = args;

    if (resource.includes('/log?')) {
	console.log("Blocked: "+resource);
	resource = "https://[::]";
	return true;
    }
    else {
	console.log("Allowed: "+resource);
    }

    const response = await originalFetch(resource, config);

    return response;
}


// Block gen_204 beacons

function blockTrackingBeacons() {
     console.log("Blocked tracking beacon!");
     return true;
}

Object.defineProperty(window.Navigator.prototype, 'sendBeacon', {
  get: function() {
    return blockTrackingBeacons;
  },
  set: function(ignored) { }
});


// Alternative implementation taken from "Don't track me google" userscript

//(function() {
//        var navProto = window.Navigator.prototype;
//        var navProtoSendBeacon = navProto.sendBeacon;
//        if (!navProtoSendBeacon) {
//            return;
//        }
//        var sendBeacon = Function.prototype.apply.bind(navProtoSendBeacon);
//
//        navProto.sendBeacon = function(url, data) {
//             console.log("Blocked tracking beacon!");
//             return true;
//       };
//})();
