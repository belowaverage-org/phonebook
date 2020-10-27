# phonebook
🔎 A fast, tag based, flat file, PHP Phone Book.

### How to use this image:
Pulling the image:
```
docker pull belowaverageorg/phonebook
```
Running the image:
```
docker run -p 8081:80 -l PhoneBook -d belowaverageorg/phonebook
```
Paths to mount for persistence:
* /var/www/html/data
* /var/www/html/api/schema.cfg.json  (Optional)