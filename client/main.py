import os
import requests
import time
import platform
code = ""
url = 'https://yourwebsite.com/reg.php?code='
key = ""

def code_input():
    os.system("cls")
    global code
    code = input("Please enter the 6 digit code: ")
    if len(code) != 6:
        os.system("cls")
        print("This is not a valid code")
        time.sleep(1)
        os.system("cls")
        code_input()
    else:
        os.system("cls")
        get_key()

def get_key():
    global url, code, key
    url = url+code
    request = requests.get(url)
    key = request.text
    if key == "001":
        print("I don't know how you go here but this won't activate it. \nCan you please email me whatever code you used fonoxy1@gmail.com")
        time.sleep(2)
        code_input()
    elif key == "002":
        print("This code has already been used please contact youremail@gmail.com to get a new code.")
        time.sleep(2)
        code_input()
    elif key == "003":
        print("This code is not a valid code.")
        time.sleep(2)
        code_input()
    else:
        user_info()

def user_info():
    global url
    url = url+"&json=true"
    request_data = requests.get(url)
    user_data = request_data.json()
    username = user_data.get("username")
    operating_system = user_data.get("os")
    edition = user_data.get("edition")
    if platform.release() != operating_system:
        print(f"This liscence key is meant for Windows {operating_system} but yor computer is running Windows {platform.release()}\nPlease email fonoxy1@gmail.com for the correct code.")
        time.sleep(5)
        code_input()
    else:
        print(f"Your name is {username} \nYour operating system is Windows {operating_system} \nYour Edition is {edition} \nIs this correct?")
        response = input("Yes/No ")
        if response == "Yes" or "yes" or "y":
            os.system("cls")
            activate()
        else:
            if response == "No" or "no" or "n":
                os.system("cls")
                print("Please conact youremail@gmail.com for the correct code.")
                time.sleep(2) 
                
def activate():
    os.system(f"slmgr.vbs -ipk {key}")
    print("KMS key set succesfully")
    os.system("slmgr.vbs -skms au.ldtp.com")
    print("KMS Host set succesfully")
    os.system("slmgr.vbs -ato")
    print("Windows Activated Succesfully \nThis window will close in 3 seconds")
    time.sleep(3)
    os.system('exit')

code_input()