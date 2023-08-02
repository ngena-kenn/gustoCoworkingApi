const express =require("express")
const app = express()
require("dotenv").config()
const stripe = require("stripe")(process.env.STRIPE_SECRET_TEST)
const bodyParser = require("body-parser")
const cors = require("cors")
const mysql = require("mysql")
const serverless = require("serverless-http")

app.use(bodyParser.urlencoded({extended : true }))
app.use(bodyParser.json())
app.use(express.json())
app.use(cors())

const db = mysql.createConnection({
	host:"localhost",
	user:"root",
	port:"3306",
	password:"",
	database:"reservationlist",
});
app.post('/reservationlist',async (req,res) => {
	const sql = "INSERT INTO liste (`name`,`email`,`date`,`price`) VALUES(?)"
	const values = [
		req.body.name,
		req.body.email,
		req.body.date,
		req.body.price
	]
	await db.query(sql, [values],(err, data) =>{
		if(err){
			console.log(err)
		}
		
		return res.json(data);
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


app.listen(process.env.PORT, ()=>{
    console.log("server is listening")
})

module.exports.handler = serverless(app)