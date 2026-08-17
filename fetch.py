import urllib.request
import re

url = "https://laravel.com/docs/master/releases"
try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    response = urllib.request.urlopen(req)
    html = response.read().decode('utf-8')

    matches = re.findall(r'Laravel 1[23].{0,100}?', html)
    print("Docs matches:", matches[:5])
except Exception as e:
    print("Error:", e)

url2 = "https://laravel-news.com/laravel-13-released"
try:
    req2 = urllib.request.Request(url2, headers={'User-Agent': 'Mozilla/5.0'})
    response2 = urllib.request.urlopen(req2)
    html2 = response2.read().decode('utf-8')
    print("News length:", len(html2))
    if "March 17, 2026" in html2:
        print("Date found in news!")
except Exception as e:
    print("Error2:", e)
