const express =require("express")
const app = express()
require("dotenv").config()
const stripe = require("stripe")(process.env.STRIPE_SECRET_TEST)
const bodyParser = require("body-parser")
const cors = require("cors")
const mysql = require("mysql")



app.use(bodyParser.urlencoded({extended : true }))
app.use(bodyParser.json())
app.use(express.json())
app.use(cors())

const url = "mysql://root:4qsb0qXciqB4gkwgaido@containers-us-west-133.railway.app:7578/railway"

const db = mysql.createConnection(

	{
	host:process.env.MYSQLHOST,
	user:process.env.MYSQLUSER,
	port:process.env.MYSQLPORT,
	password: process.env.MYSQLPASSWORD,
	database:process.env.MYSQLDATABASE,
}
);
app.post('/reservationlist',async (req,res) => {
	const sql = "INSERT INTO liste (`name`,`email`,`date`,`price`,`article`) VALUES(?)"
	const values = [
		req.body.name,
		req.body.email,
		req.body.date,
		req.body.price,
		req.body.article

	]
	await db.query(sql, [values],(err, data) =>{
		if(err){
			console.log(err)
		}
		
		return res.json(data);
	})
})
app.post('/reservationuser',async (req,res) => {
	const sql = "SELECT * FROM liste WHERE  email = ? ";
	const values = [
		req.body.email,
	]
	await db.query(sql, [values],(err, data) =>{
		if(err){
			console.log(err)
		}
		if(data.length > 0){
			return res.json(data);
		} else {
			return res.send({message: "Faile"});
		}
	})
})

app.post("/payment", cors(), async (req, res) => {
	let { amount, id } = req.body
	try {
		const payment = await stripe.paymentIntents.create({
			amount,
			currency: "EUR",
			description: "gusto coffee compagny" ,
			payment_method: id,
			confirm: true
		})
		console.log("Payment", payment)
		res.json({
			message: "Payment successful",
			success: true
		})
	} catch (error) {
		console.log("Error", error)
		res.json({
			message: "Payment failed",
			success: false
		})
	}
})


/*app.listen(process.env.PORT || 4000 , ()=>{
    console.log("server is listening")
})*/
