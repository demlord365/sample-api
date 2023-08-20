# sample-api

#Runnign project
- docker-compose up -d
#Import sql dump from dumps folder

#Open http://localhost/ in your brower

#Routes list

/auth/sign-in - get access token and  store user,required fileds: username,email

#For requests in the api group, you need to send a token in the header "Authorization"

/api/product/buy_item - product purchase,required fileds: item_id

/api/product/rent - product rent, required fileds: item_id, rent_start (Y-m-d H:i:s) , rent_end (Y-m-d H:i:s)

/api/rent/extend - rent extending, required fileds: item_id, rent_end (Y-m-d H:i:s)

/api/product/check_status - checking the status of the goods (rented, purchased) and getting a unique code, item_id
