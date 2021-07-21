# phonebook
🔎 A fast, tag based, flat file, PHP Phone Book.

### Demo
[https://phonebook-demo.belowaverage.org](https://phonebook-demo.belowaverage.org)

### How to use this image:
Pulling the image:
```
docker pull ghcr.io/belowaverage-org/phonebook:latest
```
Running the image:
```
docker run -p 8081:80 -l PhoneBook -d ghcr.io/belowaverage-org/phonebook:latest
```
Paths to mount for persistence:
* /var/www/html/data
* /var/www/html/api/schema.cfg.json  (Optional)
