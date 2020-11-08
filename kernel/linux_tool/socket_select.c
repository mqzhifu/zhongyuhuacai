#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <stdlib.h>
#include <arpa/inet.h>
#include <sys/time.h>
#include <sys/socket.h>
#include <sys/select.h>

#define BUF_SIZE 100

void error_handing(char* buf)
{
    fputs(buf, stderr);
    fputc('\n', stderr);
    exit(1);
}

int main(int argc, char* argv[]){


    int serv_sock, clnt_sock;
    struct sockaddr_in serv_adr, clnt_adr;
    struct timeval timeout;
    fd_set reads, cpy_reads;

    socklen_t adr_sz;

    int fd_max, str_len, fd_num, i;

    char buf[BUF_SIZE];

    serv_sock = socket(PF_INET, SOCK_STREAM, 0);



}