// server.js
import express from "express";
import cors from "cors";
import nodemailer from "nodemailer";
import mysql from "mysql2";

const app = express();
app.use(cors({ origin: "http://localhost:8000" })); // ✅ Allow PHP site
app.use(express.json());

// ✅ MySQL connection
const db = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "Rohan@2005",
  database: "safespace"
});

db.connect((err) => {
  if (err) console.error("❌ MySQL connection failed:", err);
  else console.log("✅ Connected to MySQL database");
});

// ✅ Gmail SMTP setup
const transporter = nodemailer.createTransport({
  service: "gmail",
  auth: {
    user: "ecorewards48@gmail.com",  // ✅ your Gmail
    pass: "zubr gspc eorb fyxc"      // ✅ Gmail App Password
  },
});

// ✅ SOS route (matches sos.php)
app.post("/send-sos", async (req, res) => {
  const { userName, trustedEmail, location } = req.body;


  if (!userName || !trustedEmail) {
    return res.status(400).json({
      success: false,
      message: "❌ Missing user name or trusted email.",
    });
  }

  try {
    const mailOptions = {
  from: '"SafeSpace Alerts" <ecorewards48@gmail.com>',
  to: trustedEmail,
  subject: `🚨 SOS Alert from ${userName}`,
  html: `
  <div style="font-family:Arial,sans-serif">
    <h2>🚨 SOS Alert</h2>
    <p><b>${userName}</b> has triggered an SOS alert via <b>SafeSpace</b>.</p>
    ${
      location
        ? `<p><b>Last known location:</b> <a href="${location}">${location}</a></p>`
        : `<p>Location unavailable.</p>`
    }
    <p>Please reach out to them immediately.</p>
    <p style="font-size:13px;color:#777;">— SafeSpace Alert System</p>
  </div>
`,
};


    await transporter.sendMail(mailOptions);
    console.log(`✅ SOS sent to ${trustedEmail}`);
    res.json({ success: true, message: `✅ SOS sent to ${trustedEmail}` });
  } catch (err) {
    console.error("❌ Email error:", err);
    res.status(500).json({ success: false, message: "❌ Failed to send SOS email." });
  }
});

// ✅ Start server
app.listen(5000, () => {
  console.log("🚀 Express server running on http://localhost:5000");
});
