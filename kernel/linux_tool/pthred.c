#include <stdio.h>  
#include <pthread.h>

#include "client.c"

void thread_1(void * num)  
{  
	int n = *(int *)num;
    int i=0;  
    for(i=0;i<=n;i++)  
    {  
		send_sock();
    }  

	pthread_exit(0);
}  
  
int main(void)  
{  
    pthread_t id_1,id_2;  
    int i,ret;  
	printf("this is main...");

	create_sub();

    return 0;  
}  

int create_sub( ){
	int ret,num=10;
	pthread_t id_1;
	ret=pthread_create(&id_1,NULL,(void  *) thread_1,& num);
    if(ret!=0)
    {
        printf("Create pthread error!\n");
    	return -1;
	}
	pthread_join(id_1,NULL);

}

