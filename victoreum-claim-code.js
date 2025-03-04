export default {
  async fetch(request, env) {
    const FIREBASE_API_URL = "https://firestore.googleapis.com/v1/projects/victoreumgames-drop/databases/(default)/documents/claim_code/code";
    const FIREBASE_API_KEY = "AIzaSyBB3gfZtcvPBs1G4fbezMU9EdeK6u261To";

    function generateCode() {
      const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      let code = "";
      for (let i = 0; i < 7; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return code;
    }

    const newCode = generateCode();

    const requestBody = {
      fields: {
        code: { stringValue: newCode },
        updated_at: { timestampValue: new Date().toISOString() }
      }
    };

    const response = await fetch(`${FIREBASE_API_URL}?key=${FIREBASE_API_KEY}`, {
      method: "PATCH",  // Use PATCH to update instead of adding a new document
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestBody)
    });

    if (response.ok) {
      return new Response(`Successfully updated claim code: ${newCode}`, { status: 200 });
    } else {
      return new Response(`Failed to update claim code: ${await response.text()}`, { status: response.status });
    }
  }
};
