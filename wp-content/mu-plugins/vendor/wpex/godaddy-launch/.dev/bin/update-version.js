const fs = require('fs').promises;
const path = require('path');
const packageJson = require('../../package.json');

const filesToUpdate = [
	{
		filePath: 'godaddy-launch.php',
		searchPattern: /Version.*/,
		replacePattern: `Version: ${packageJson.version}`,
	},
    {
		filePath: 'includes/Application.php',
		searchPattern: /const VERSION.*/,
		replacePattern: `const VERSION = '${packageJson.version}';`,
	},
];

async function updateVersionInFiles() {
	for (const file of filesToUpdate) {
		try {
			const filePath = path.join(__dirname, '..', '..', file.filePath);
			const data = await fs.readFile(filePath, 'utf8');

			const updatedData = data.replace(file.searchPattern, file.replacePattern);

			await fs.writeFile(filePath, updatedData, 'utf8');
			console.log(`Updated version in ${file.filePath}`);
		} catch (error) {
			console.error(`Error updating ${file.filePath}: ${error.message}`);
		}
	}
}

updateVersionInFiles().then(() => {
	console.log('Version update process completed.');
}).catch(error => {
	console.error(`An error occurred: ${error.message}`);
});
