#include "SPI.h"
#include "Phpoc.h"

PhpocServer server(80);
boolean alreadyConnected = false; 

void setup() {
    Serial.begin(9600);
    while(!Serial)
        ;

    Phpoc.begin(PF_LOG_SPI | PF_LOG_NET);

    server.beginWebSocket("game");

    Serial.print("WebSocket server address : ");
    Serial.println(Phpoc.localIP());
    
    pinMode(6, INPUT); 
    pinMode(7, INPUT); 
    pinMode(8, INPUT); 
    pinMode(9, INPUT); 
    
}

int value_6 = digitalRead(6);
int value_7 = digitalRead(7);
int value_8 = digitalRead(8);
int value_9 = digitalRead(9);
int pre_dir_1 = 0;
int pre_dir_2 = 0;
int dir_1 = 0;
int dir_2 = 0;

void loop() {
    // when the client sends the first byte, say hello:
    PhpocClient client = server.available();
    Serial.println(client);
    if (client) {
        value_6 = digitalRead(6);
        value_7 = digitalRead(7);
        value_8 = digitalRead(8);
        value_9 = digitalRead(9);
        dir_1 = value_7 - value_6;
        dir_2 = value_9 - value_8;

        if(dir_1 != pre_dir_1 || dir_2 != pre_dir_2)
        {
            pre_dir_1 = dir_1;
            pre_dir_2 = dir_2;
            
            String txtMsg = "[" + String(dir_1) + ", " + String(dir_2) + "]\r\n";  
            char buf[txtMsg.length()+ 1];
            txtMsg.toCharArray(buf, txtMsg.length() + 1);
            server.write(buf, txtMsg.length());
        }         
    }
}
