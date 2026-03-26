# Disclaimer

**This project is 100% AI-free. All code including this README has been written by me or with the help of other humans.**

# gsearch_proxy

This is a self-hostable proxy for google.com, which blocks trackers, ads and AI overviews. It also hides your user agent and IP address. Google will still see your server's IP, so it is recommended to host it on a VPS rather than your own home server.

## How is this different from other "proxies"?

First of all, this proxy runs mostly on the client side. Your server only loads the initial HTML page using `curl`, and the rest happens in your browser. No complex crawlers or headless browsers are used.

Also, it focuses on using DNS rewrites. If you use a DNS service like NextDNS or Pi-hole, you can point `www.google.com` to your server's IP using a DNS rewrite, and it will always open your proxied page when you go to Google! (An HTTPS warning will appear for this, but keep reading to solve it)

## Self-hosting (using nginx)

**Note:** First make sure that you have the latest version of `nginx-full`, and `php-fpm` and `composer` is installed. The default config (`conf/nginx/google.conf`) also assumes that php-fpm runs on `127.0.0.1:9000`. Edit it if needed.

1. Clone this repo:

```
git clone https://github.com/IsHacker003/gsearch_proxy && cd gsearch_proxy
```

2. Modify the config and copy it into `/etc/nginx/sites-enabled`:

```
sed -i -e 's#/usr/share/nginx/html/google#'$(pwd)'#g' conf/nginx/google.conf
cp conf/nginx/google.conf /etc/nginx/sites-enabled
```
3. Restart nginx

Done! Now your proxy is running. Now you need to set up the DNS rewrite.

# Setting up DNS rewrite

Here I will only show the steps for NextDNS and Pi-hole, as I have not used any other DNS services.

## NextDNS

### Static IP

1. Go to Settings tab in your NextDNS dashboard and click on New Rewrite under DNS Rewrites.
2. Enter `www.google.com` as domain and your server's IP as answer (e.g `192.168.1.128`)
3. DNS rewrite has been set successfully, now wait a few minutes for the existing DNS cache to get flushed before opening Google.

![image](https://raw.githubusercontent.com/IsHacker003/IsHacker003/refs/heads/master/images_external/NextDNS-staticip-gsearchproxy.jpg)

**NOTE:** You can create multiple rewrites for the same domain with different answers. So, you can create another rewrite with your server's **IPv6** address as answer.

### Dynamic IP (DDNS)

If your server's IP keeps changing (e.g for IPv6), you can set up DDNS and use your DDNS domain as the answer for the rewrite. [Afraid.org](https://freeddns.afraid.org) offers both IPv4 and IPv6 DDNS.

![image](https://raw.githubusercontent.com/IsHacker003/IsHacker003/refs/heads/master/images_external/NextDNS-ddns-gsearchproxy.jpg)
