
# grs-upbit

**Install dependencies** 

     sudo add-apt-repository ppa:ondrej/php && sudo apt update
     sudo apt install php8.1 php8.1-fpm php8.1-curl php8.1-memcached memcached nginx -y
    
   **Clone and deploy**

      git clone https://github.com/Groestlcoin/grs-upbit
      cd grs-upbit
      mv index.php /var/www/html/
**Setup your certificates**

    sudo apt-get install software-properties-common -y
    sudo add-apt-repository ppa:certbot/certbot && sudo apt-get update
    sudo apt-get install certbot python-certbot-nginx -y
    certbot --nginx -d upbit.groestlcoin.org

**Setup your cron job to get the data every minute**

    crontab -e
    Add end of file: 15 3 * * * /usr/bin/certbot renew --quiet
    Add end of file: * * * * * /usr/bin/php /root/grs-upbit/getdata.php

**config your nginx server**
sudo nano /etc/nginx/sites-available/default and replace with:

    server {
    
            listen 80 ;
            listen [::]:80 ;
            listen [::]:443 ssl ipv6only=on; # managed by Certbot
            listen 443 ssl; # managed by Certbot
            ssl_certificate /etc/letsencrypt/live/upbit.groestlcoin.org/fullchain.pem; # managed by Certbot
            ssl_certificate_key /etc/letsencrypt/live/upbit.groestlcoin.org/privkey.pem; # managed by Certbot
            include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
            ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot
    
            server_name upbit.groestlcoin.org;
    
            # force https-redirects
            if ($scheme = http) {
               return 301 https://upbit.groestlcoin.org$request_uri;
            }
    
            root /var/www/html;
            index index.php index.html index.htm index.nginx-debian.html;

			location /GRS [^/]\.php(/|$) {
	            fastcgi_split_path_info  ^(.+\.php)(/.+)$;
	            fastcgi_index index.php;
	            fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
	            include fastcgi_params;
	            fastcgi_param  PATH_INFO $fastcgi_path_info;
	            fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
            }
    
            location ~ [^/]\.php(/|$) {
	            fastcgi_split_path_info  ^(.+\.php)(/.+)$;
	            fastcgi_index index.php;
	            fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
	            include fastcgi_params;
	            fastcgi_param  PATH_INFO $fastcgi_path_info;
	            fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
            }
    }
**Restart/reload your nginx server**

    service nginx reload && service php8.1-fpm restart
