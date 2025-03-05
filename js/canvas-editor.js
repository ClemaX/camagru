class DrawCommand {
	/**
	 *
	 * @param {HTMLImageElement} image
	 * @param {{x: number, y: number}} position
	 * @param {{x: number, y: number}} dimensions
	 */
  constructor(image, position, dimensions) {
    this.image = image;
    this.position = position;
    this.dimensions = dimensions;
		this.layerId = undefined;
  }

	/**
	 * Execute the command.
	 *
	 * @param {CanvasEditor} editor
	 */
  execute(editor) {
		this.layerId = editor.addLayer(this.image, this.position, this.dimensions);
		editor.redraw();
  }

	/**
	 * Undo the command.
	 *
	 * @param {CanvasEditor} editor
	 */
  undo(editor) {
		editor.removeLayer(this.layerId);
		editor.redraw();
	}
}

class CanvasEditor {
	/**
	 * @param {HTMLCanvasElement} canvas
	 */
	constructor(canvas) {
		this.canvas = canvas;
		this.context = canvas.getContext('2d');

		this.context.imageSmoothingEnabled = false;

    this.layers = [];
    this.undoStack = [];
    this.redoStack = [];

		canvas.addEventListener('dragover', (e) => {
			e.preventDefault();
		});

		canvas.addEventListener("drop", (e) => {
			e.preventDefault();

			const files = e.dataTransfer.files;

			var dropX = e.offsetX;
			var dropY = e.offsetY;

			var dragOffsetX = 0;
			var dragOffsetY = 0;

			var scaledWidth = 0;
			var scaledHeight = 0;

			const img = new Image();

			img.onload = (e) => {
				const canvasRect = canvas.getBoundingClientRect();

				const canvasScale = canvas.width / canvasRect.width;

				const width = scaledWidth * canvasScale;
				const height = scaledHeight * canvasScale;

				const x = (dropX - dragOffsetX) * canvasScale;
				const y = (dropY - dragOffsetY) * canvasScale;

				this.context.drawImage(img, x, y, width, height);
			}

			if (files.length === 1 && files[0].type.startsWith('image/')) {
				const file = files[0];
				const reader = new FileReader();

				reader.onload = (e) => {
					img.src = e.target.result;
				};

				reader.readAsDataURL(file);
			}
			else {
				try {
					const { offsetX, offsetY, width, height } = JSON.parse(e.dataTransfer.getData('application/sticker'));

					dragOffsetX = offsetX;
					dragOffsetY = offsetY;

					scaledWidth = width;
					scaledHeight = height;
				}
				catch {
					console.debug("Unsupported drop:", e, files);
					return;
				}

				img.src = e.dataTransfer.getData('text/uri-list');
			}
		});
	}

	/**
	 * Execute a command.
	 * @param {DrawCommand} command
	 */
	executeCommand(command) {
    command.execute(this);
    this.undoStack.push(command);
    this.redoStack = [];
  }

	/** Undo the previous command */
	undo() {
    if (this.undoStack.length > 0) {
      const command = this.undoStack.pop();
      command.undo(this);
      this.redoStack.push(command);
    }
  }

	/**
	 * Redo the previous undone command.
	 */
  redo() {
    if (this.redoStack.length > 0) {
      const command = this.redoStack.pop();
      command.execute(this);
      this.undoStack.push(command);
    }
  }

	/**
	 *
	 * @param {HTMLElement} image
	 * @param {number} sourceX
	 * @param {number} sourceY
	 * @param {number} width
	 * @param {number} height
	 */
	setBackgroundImage(image, sourceX, sourceY, width, height) {
		this.canvas.width = width;
		this.canvas.height = height;

		this.context.drawImage(image, sourceX, sourceY, width, height,
			0, 0, canvas.width, canvas.height);
	}

	/**
	 * Add a layer.
	 *
	 * @param {HTMLImageElement} image
	 * @param {{x: number, y: number}} position
	 * @param {{x: number, y: number}} dimensions
	 * @return {number} the newly added layer's id
	 */
	addLayer(image, position, dimensions) {
		const layerId = crypto.randomUUID();

		this.layers.push({id: layerId, image, position, dimensions});

		return layerId;
	}

	/**
	 * Remove a layer.
	 *
	 * @param {number} layerId
	 */
	removeLayer(layerId) {
		this.layers = this.layers.filter((layer) => layer.id !== layerId);
	}

	/**
	 * Redraw layers on the canvas.
	 */
	redraw() {
		for (const layer of layers) {
			this.context.drawImage(layer.image, layer.position.x, layer.position.y,
					layer.dimensions.x, layer.dimensions.y);
		}
	}
}
