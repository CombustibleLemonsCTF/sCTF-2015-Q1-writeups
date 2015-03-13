# sCTF 2015 Q1: Every 7 Seconds

**Points:** 65
**Description:**

> Sometimes, you gotta be quick.

## Write-up

The link that the description provides takes you back to the [problems page](http://compete.sctf.io/problems.php), meaning that the flag must be hidden on that page. As with any web challenge, you should open up your browser's developer's tools (we used Chrome). Chrome's developer tools has a host of useful tools, including a Javascript console and a resource viewer.

The problem title implies that you have a limited amount of time to view the flag, which necessitates constant refreshing. Eventually, you might stumble upon a suspiciously named cookie named `QuicklyNow`, which has a value of `7730775f796f755f6172655f737065656479`.

This hexadecimal string can be converted to ASCII to get the flag.

```
$ python3
Python 3.3.2+ (default, Feb 28 2014, 00:52:16) 
[GCC 4.8.1] on linux
Type "help", "copyright", "credits" or "license" for more information.
>>> import binascii
>>> binascii.unhexlify(b'7730775f796f755f6172655f737065656479')
b'w0w_you_are_speedy'
```

## Other write-ups and resources

* http://en.wikipedia.org/wiki/HTTP_cookie
