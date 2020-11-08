/*socket tcp服务器端*/

//引入相关函数文件
#include <sys/stat.h>
#include <fcntl.h>
#include <errno.h>
#include <netdb.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>

#define SERVER_PORT 5555

//错误处理函数
void error(char *msg , int code) {
    fprintf(stderr, "Error: %s  %s", msg, strerror(errno));
    exit(1);
}


/*
    创建socket函数FD，失败返回-1
    int socket(int domain, int type, int protocol);
    参数1：使用的地址类型，一般都是ipv4，AF_INET
    参数1：套接字类型，tcp：面向连接的稳定数据传输SOCK_STREAM
    参数3：设置为0
*/
int open_listener_socket() {
    int s = socket(PF_INET, SOCK_STREAM, 0);
    if (s == -1) {
        error("Can't open socket",-4);
    }
    return s;
}


// 绑定端口
void bind_to_port(int socket, int port) {

    //对于bind，accept之类的函数，里面套接字参数都是需要强制转换成(struct sockaddr *)
    //bind三个参数：服务器端的套接字的文件描述符，



    struct sockaddr_in name;
    //初始化服务器端的套接字，并用htons和htonl将端口和地址转成网络字节序
    name.sin_family = PF_INET;
//    name.sin_family = AF_INET;
    name.sin_port = (in_port_t)htons(port);
//    name.sin_port = htons(SERVER_PORT);

    //ip本地ip或用宏INADDR_ANY，代表0.0.0.0，所有地址
    name.sin_addr.s_addr = htonl(INADDR_ANY);

     //正常一个程序绑定一个端口取消后，30秒内不允许再次绑定，防止ctrl+c无效
    int reuse = 1;
    if (setsockopt(socket, SOL_SOCKET, SO_REUSEADDR, (char *)&reuse, sizeof(int)) == -1) {
        error("Can't set the reuse option on the socket",-2);
    }
    int c = bind(socket, (struct sockaddr*)&name, sizeof(name));
    if (c == -1) {
        error("Can't bind to socket",-1);
    }
}

// 向客户端发消息
int send_data(int socket, char *s) {
    int result = (int)send(socket, s, strlen(s), 0);
    if (result == -1) {
        fprintf(stderr, "%s: %s \n","和客户端通信发生错误",strerror(errno));
    }
    return result;
}



void main(){
    //调用socket函数返回的文件描述符
	int serverSocket;
    //声明两个套接字sockaddr_in结构体变量，分别表示客户端和服务器
    struct sockaddr_in server_addr;
    struct sockaddr_in clientAddr;
    int addr_len = sizeof(clientAddr);
    int client;
    char buffer[200];
    int iDataNum;
    //计数器，无用
    int cnt = 0;


    //创建socket
    serverSocket = open_listener_socket();

	printf("create socket ok.\n");
    //绑定IP端口
    bind_to_port(serverSocket,SERVER_PORT);

    printf("bind socket ok.\n");


    //设置服务器上的socket为监听状态
    //第2个参数：backlog，半连接+已连接，之和
    if(listen(serverSocket, 5) < 0)
    {
        error("listen",-3);
    }


    printf("Listening on port: %d\n", SERVER_PORT);

    while(1){
        //调用accept，会进入阻塞状态,accept返回一个套接字FD，便有两个FD:serverSocket和client
        //serverSocket仍然继续在监听状态，client则负责接收和发送数据

        //clientAddr是一个传出参数，accept返回时，传出客户端的地址和端口号
        //addr_len是一个传入-传出参数，传入的是调用者提供的缓冲区的clientAddr的长度，以避免缓冲区溢出。
        //传出的是客户端地址结构体的实际长度。
        //出错返回-1

        client = accept(serverSocket, (struct sockaddr*)&clientAddr, (socklen_t*)&addr_len);
        if(client < 0){
            error("accept",-5);
            continue;
        }

        printf("accept client:%d.\n",client);


        //inet_ntoa   ip地址转换函数，将网络字节序IP转换为点分十进制IP
        //表达式：char *inet_ntoa (struct in_addr);
        printf("IP is %s\n", inet_ntoa(clientAddr.sin_addr));
        printf("Port is %d\n", htons(clientAddr.sin_port));


        //定义进程ID变量，启用多进程模式，防止阻塞
        pid_t pid;
        pid = fork()
        if(pid < 0){
            error("fork error",-9);
        }else if(pid == 0){

            char final_recv_data[255];
//            while(1){
                iDataNum = recv(client, buffer, 1024, 0);
                if(iDataNum < 0)
                {
                    error("recv error",-6);
                }

//                if(iDataNum == 0){
//                    break;
//                }


                strcat(final_recv_data,buffer);

                //这里防止死循环，也是防止C端恶意攻击
//                cnt++;
//                if(cnt > 10){
//                    printf(" err,cnt>10 exec!");
//                    break;
//                }
            }


            printf("recv_str_num:%d,recv data is: %s,send_data:%s\n", strlen(final_recv_data), final_recv_data,"yes!");
            char send_data_arr[] = "yes,im z!";

            send_data(client,send_data_arr);
        }else{
            //父进程，不做任何操作，返回
        }
    }

}


